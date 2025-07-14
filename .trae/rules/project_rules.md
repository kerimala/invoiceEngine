# Project Rules for Agentic Development

## Core Development Principles

### Test-Driven Development (TDD)
- Write tests before implementation
- Follow Red-Green-Refactor cycle
- Maintain high test coverage (>80%)
- Use descriptive test names that explain behavior

### Modular Architecture
Apply these fundamental principles:

#### SOLID Principles
- **Single Responsibility**: Each class/module has one reason to change
- **Open/Closed**: Open for extension, closed for modification
- **Liskov Substitution**: Derived classes must be substitutable for base classes
- **Interface Segregation**: Clients shouldn't depend on unused interfaces
- **Dependency Inversion**: Depend on abstractions, not concretions

#### Design Patterns & Practices
- **DRY** (Don't Repeat Yourself): Eliminate code duplication
- **KISS** (Keep It Simple, Stupid): Favor simplicity over complexity
- **YAGNI** (You Ain't Gonna Need It): Don't build features until needed
- **DDD** (Domain-Driven Design): Model software around business domain

#### Clean Code Standards
Follow Robert C. Martin's principles:
- **Clean Code**: Write readable, maintainable code
- **Clean Architecture**: Separate concerns with clear boundaries
- **Clean Coder**: Professional development practices

## Agentic AI Development Guidelines

### Autonomous Decision Making
- Implement clear decision trees for common scenarios
- Use structured logging for AI agent actions and reasoning
- Maintain audit trails for all automated changes
- Implement rollback mechanisms for critical operations

### Code Quality & Safety
- Always validate inputs and outputs
- Implement circuit breakers for external dependencies
- Use defensive programming practices
- Never commit secrets or sensitive data
- Implement proper error handling and graceful degradation
- Never ignore dependency warnings - address them promptly to maintain security and stability

### Iterative Improvement
- Learn from previous implementations and feedback
- Continuously refactor based on new requirements
- Document lessons learned and best practices
- Maintain version history for major architectural decisions

### Communication & Documentation
- Use clear, descriptive commit messages
- Document API contracts and interfaces
- Maintain up-to-date README files
- Use inline comments for complex business logic only

## Project-Specific Guidelines

### Invoice Engine Architecture
- Follow modular monolith design principles with independent packages in `/packages` directory
- Refer to technical documentation in `/docs` folder for architecture specifications:
  - ARCHITECTURE.md - Core system design and event flow
  - PRICING_LOGIC.md - Pricing calculation specifications
  - ENRICHED_INVOICE_LINES.md - Data structure documentation
  - AGREEMENT_SERVICE.md - Agreement management specifications

### Event-Driven Architecture
- Use Laravel Event Sourcing for package communication
- Follow the established event chain: FileStored → CarrierInvoiceLineExtracted → PricedInvoiceLine → InvoiceAssembled
- Implement proper event listeners and dispatchers
- Maintain loose coupling between packages through events

### Invoice Processing Pipeline
- Support multiple file formats (.xlsx, .csv, .txt)
- Implement robust error handling for malformed invoice files
- Apply pricing rules through the PricingEngine package
- Generate locale-aware PDF outputs with proper formatting
- Use asynchronous processing for file uploads and heavy operations

### Security & Data Handling
- Validate all uploaded invoice files for security threats
- Implement proper input sanitization for invoice data
- Use secure file storage for temporary invoice files
- Follow data protection regulations for customer invoice data
- Implement proper authentication and authorization for invoice access

### Performance & Scalability
- Optimize for large invoice file processing
- Implement efficient caching strategies for pricing rules and agreements
- Use Laravel queues for asynchronous invoice processing
- Monitor and profile critical invoice processing paths
- Support locale-based formatting without performance degradation

## File Organization

### Directory Structure
```
├── app/                # Laravel application shell
├── packages/           # Internal microservice packages
│   ├── AgreementService/
│   ├── InvoiceFileIngest/
│   ├── InvoiceParser/
│   ├── PricingEngine/
│   ├── InvoiceAssembler/
│   ├── PdfRenderer/
│   ├── InvoiceSender/
│   └── UnitConverter/
├── tests/              # Integration and feature tests
├── docs/               # Technical documentation
├── database/           # Migrations, seeders, factories
├── resources/          # Views, assets, localization
├── storage/            # File storage and logs
├── .taskmaster/        # Task management
├── .trae/              # Development rules and workflows
└── README.md           # Project overview
```

### Naming Conventions
- Use descriptive, self-documenting names
- Follow language-specific conventions (camelCase, snake_case, etc.)
- Use consistent prefixes for related functionality
- Avoid abbreviations unless widely understood

## Development Workflow

### Feature Development
1. Create feature branch from `main`
2. Implement feature in appropriate package(s)
3. Add PestPHP tests for new functionality
4. Update package documentation and event flow diagrams
5. Test event integration between packages
6. Submit pull request with comprehensive description
7. Code review focusing on package boundaries and event handling
8. Merge to `main`

### Bug Fixes
1. Create hotfix branch
2. Identify affected package(s) and event flow
3. Implement fix with regression tests
4. Test invoice processing pipeline end-to-end
5. Fast-track review process
6. Deploy to staging for verification with real invoice files
7. Merge and deploy to production

### Testing Strategy
- **Package Tests**: Unit tests for each package's business logic
- **Event Tests**: Integration tests for event sourcing and package communication
- **Pipeline Tests**: End-to-end tests for complete invoice processing workflows
- **File Format Tests**: Comprehensive tests for .xlsx, .csv, and .txt file parsing
- **Performance Tests**: Load testing with large invoice files and concurrent processing
- **Locale Tests**: Validation of formatting across different locales and currencies

## Quality Gates

### Before Committing
- [ ] All PestPHP tests pass (both package and integration tests)
- [ ] Code coverage meets threshold (>80%)
- [ ] Laravel Pint code style checks pass
- [ ] PHPStan static analysis passes
- [ ] No security vulnerabilities in dependencies
- [ ] Package documentation updated
- [ ] Event flow documentation updated if applicable

### Before Deployment
- [ ] Full invoice processing pipeline tests pass
- [ ] File upload and parsing tests with various formats pass
- [ ] PDF generation and locale formatting tests pass
- [ ] Performance benchmarks met for large invoice files
- [ ] Security review completed for file handling
- [ ] Database migrations tested
- [ ] Queue worker functionality verified
- [ ] Rollback plan documented
- [ ] Monitoring alerts configured for invoice processing errors