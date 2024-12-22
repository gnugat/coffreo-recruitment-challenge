## DevOps

```gherkin
    Scenario: DevOps
        When I check the `/doc/PLN-06-devops.md` document
        Then I should find documentation on how to deploy the app
        And the explanation for the changes made to the following files / folders:
            - `/Dockerfile`
            - `/docker-compose.yaml`
            - `/.github/workflows/`
```

## Continuous Integration

On every push to the branch `sprint-0` (the main branch of the fork),
a `test` Github Action is launch to run the automated tests and for checking
the coding standards.

See `/.github/workflows/test.yaml`.


