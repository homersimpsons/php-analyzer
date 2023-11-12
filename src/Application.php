<?php

declare(strict_types=1);

namespace App;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

use function assert;
use function is_string;

class Application extends SingleCommandApplication
{
    public function __construct()
    {
        parent::__construct('Exercism PHP Analyzer');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setVersion('1.0.0');
        $this->addArgument('exercise-slug', InputArgument::REQUIRED, 'Slug of the exercise');
        $this->addArgument('solution-dir', InputArgument::REQUIRED, 'Directory of the solution');
        $this->addArgument('output-dir', InputArgument::REQUIRED, 'Writable directory for the analysis');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exerciseSlug = $input->getArgument('exercise-slug');
        $solutionDir = $input->getArgument('solution-dir');
        $outputDir = $input->getArgument('output-dir');

        assert(is_string($exerciseSlug), 'exercise-slug must be a string');
        assert(is_string($solutionDir), 'solution-dir must be a string');
        assert(is_string($outputDir), 'output-dir must be a string');

        $logger = new ConsoleLogger($output);

        $logger->info('Exercise slug: ' . $exerciseSlug);
        $logger->info('Solution directory: ' . $solutionDir);
        $logger->info('Output directory: ' . $outputDir);

        $solutionAdapter = new LocalFilesystemAdapter($solutionDir);
        $solutionFilesystem = new Filesystem(
            $solutionAdapter,
            ['base_url' => $solutionDir],
            publicUrlGenerator: new class() implements PublicUrlGenerator {
                public function publicUrl(string $path, Config $config): string
                {
                    return $config->get('base_url') . DIRECTORY_SEPARATOR . $path;
                }
            });

        $outputAdapter = $outputDir === 'memory://'
            ? new InMemoryFilesystemAdapter()
            : new LocalFilesystemAdapter($outputDir);
        $outputFilesystem = new Filesystem($outputAdapter);

        $this->analyze($solutionFilesystem, $outputFilesystem, $logger);

        return self::SUCCESS;
    }

    public function analyze(Filesystem $solutionDir, Filesystem $outputDir, LoggerInterface $logger): void
    {
        $analyzer = new DirectoryAnalyzer($solutionDir, $logger);
        $result = $analyzer->analyze();

        $outputDir->write('/analysis.json', json_encode($result->analysisJsonSerialize()));
        $outputDir->write('/tags.json', json_encode($result->tagsJsonSerialize()));
    }
}
