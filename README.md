@TODO:
- All interactions with the Exercism website are handled automatically. Analyzers have the single responsibility of taking a solution and returning a status and any messages
  => Where is this status documentation ?
- When using parameterized files, ensure to escape all uses of % by placing anther % in front of it. e.g. Try aim for 100%% of the tests passing.
  => Does this apply to params value ?
- Check for memory constraints
- Testing:
  - One test per rules
  - One "e2e" test
- CI
  
## Architecture

```
src/
  Rules/ # PHPStan rules
    Common/ # Rules to apply for all exercises
    HelloWorld/ # Rules to apply only for "hello-world" exercise
```

### Writing a rule

Rules are the basis of the analyzer. They are PHPStan rules that are applied to the solution files.

First you should read the [PHPStan documentation](https://phpstan.org/developing-extensions/rules).

There are two types of rules:
- TagRule: used to add tags to the solution https://exercism.org/docs/building/tooling/analyzers/tags
- CommentRule: used to add comments to the solution https://exercism.org/docs/building/tooling/analyzers/comments

## Workflow

1. Retrieve the exercise meta
2. Retrieve the submitted PHP files
3. Get the list of rules to apply
4. Run PHPStan with the rules
5. Get PHPStan output
6. Transform it to a list of messages / tags
7. Dump the files
