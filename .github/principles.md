DRY (Don't Repeat Yourself): Every piece of logic or knowledge must have a single, unambiguous, authoritative representation within a system. If you find yourself copying and pasting the exact same block of code into multiple files or functions, you are violating DRY. You should extract that logic into a single shared function, method, or component.

Readability: The degree to which source code can be easily understood by a human reader—including the original author six months later. Maintaining consistent indentation, leaving helpful comments for complex logic, and avoiding overly "clever" one-liners that take five minutes to decipher.

Reusability: Designing segments of code (functions, components, or classes) so they can be used in multiple places with little or no modification. A reusable piece of code is usually highly modular and loosely coupled. It does one specific job and doesn't rely heavily on the specific context of where it was called. 

KISS (Keep It Simple, Stupid): The simpler the code, the easier it is to maintain, debug, and hand off to another developer. If you have to choose between a clever, complex one-liner and three lines of highly readable code, choose the readable code.

YAGNI (You Aren't Gonna Need It): Never build features or abstractions for hypothetical future use cases. Build exactly what is needed for the current requirements. Predictive coding usually leads to bloated, unused architecture.

Fail Fast: Code should expose errors as early as possible rather than silently failing or proceeding with bad data. For example, validating an incoming request payload immediately at the controller level before it ever hits your database logic.

Single Responsibility Principle (SRP): A class or function should have only one reason to change

Open/Closed Principle: Software entities should be open for extension, but closed for modification. You should be able to add new functionality without rewriting existing core code (often achieved through interfaces or traits).

Liskov Substitution Principle: If you swap a parent class with a child class, the application shouldn't break. The child must behave in the way the parent is expected to behave.

Interface Segregation Principle: A class shouldn't be forced to implement methods it doesn't use. It’s better to have several small, specific interfaces than one massive, general-purpose interface.

Dependency Inversion Principle: High-level modules shouldn't depend on low-level modules; both should depend on abstractions (interfaces). This is the concept behind Dependency Injection.

SoC (Separation of Concerns): Different sections of a program should handle distinct, separate tasks. 

Composition over Inheritance: Instead of building deep, fragile class hierarchies, build classes by assembling smaller, modular pieces of functionality.

Law of Demeter (Principle of Least Knowledge): A unit should have limited knowledge about other units. Objects should only talk to their immediate friends, not strangers.
