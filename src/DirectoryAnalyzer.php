<?php

declare(strict_types=1);

namespace App;

use App\Entity\AnalysisResult;
use App\Rules\Common\TagConstructIf;
use League\Flysystem\Filesystem;
use Nette\Neon\Neon;
use Psr\Log\LoggerInterface;
use RuntimeException;

use Symfony\Component\Process\Process;
use function assert;
use function implode;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * This class analyze a directory of solutions.
 *
 * Its main goal is to generate an analysis of the solutions presents in the given directory.
 */
class DirectoryAnalyzer
{
    const PHPSTAN_CONFIG_FILE_PATH = __DIR__ . '/../generated-conf.neon';

    public function __construct(
        private readonly Filesystem $solutionDir,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function analyze(): AnalysisResult
    {
        // 1. Get files list
        $solutionsFiles = $this->getSolutionsFiles();
        array_walk($solutionsFiles, fn (string &$file) => $file = $this->solutionDir->publicUrl($file));

        // 2. Get Rules list
        $rules = $this->getRules();

        // 3. Get PHPStan's config
        $phpstanConfig = $this->getPhpStanConfig($rules);
        file_put_contents(self::PHPSTAN_CONFIG_FILE_PATH, $phpstanConfig);

        // 4. Run phpstan analyse
        $phpstanProcess = new Process([
            'php',
            'vendor/bin/phpstan',
            'analyse',
            '--error-format=json',
            '--configuration=' . self::PHPSTAN_CONFIG_FILE_PATH,
            '--no-progress',
            ...$solutionsFiles
        ]);
        $phpstanProcess->run();
        $this->logger->info('PHPStan error output: ' . $phpstanProcess->getErrorOutput());
        $phpstanOutput = $phpstanProcess->getOutput();

        // 5. Transform result to Exercism format
        return AnalysisResult::fromPhpstanJson($phpstanOutput);
    }

    /**
     * @return array<string>
     */
    private function getSolutionsFiles(): array
    {
        $configJson = $this->solutionDir->read('/.meta/config.json');

        $config = json_decode($configJson, true, flags: JSON_THROW_ON_ERROR);
        assert(is_array($config), 'json_decode(..., true) should return an array');

        if (!isset($config['files']['solution']) || !is_array($config['files']['solution'])) {
            throw new RuntimeException('.meta/config.json: missing or invalid `files.solution` key');
        }

        $solutions = $config['files']['solution'];
        $this->logger->info('.meta/config.json: Solutions files: ' . implode(', ', $solutions));

        if (empty($solutions)) {
            $this->logger->warning('.meta/config.json: `files.solution` key is empty');
        }

        return $solutions;
    }

    /**
     * @return class-string[]
     */
    private function getRules(): array
    {
        $rules =  [TagConstructIf::class];
        $this->logger->info('Rules: ' . implode(', ', $rules));

        return $rules;
    }

    /**
     * @param array<string> $rules
     * @return string
     */
    private function getPhpStanConfig(array $rules): string
    {
        $config = [
            'parameters' => [
                'customRulesetUsed' => true
            ],
            'rules' => $rules
        ];

        return Neon::encode($config);
    }
}
