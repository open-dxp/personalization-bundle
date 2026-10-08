# Upgrade Notes

## 1.1.0
- [BUGFIX] The payloads and results of the handlers are no longer registered as services. A container that makes its services public failed on them
- [CHORE] Replace Codeception with Pest and `open-dxp/test-foundation`
- [CHORE] Require `open-dxp/opendxp` ^1.5
