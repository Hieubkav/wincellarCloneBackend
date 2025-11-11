## Quick Patterns

**Route:**
```ts
router.get('/users/:id', asyncHandler(userController.getUser));
router.post('/users', asyncHandler(userController.createUser));
```

**Controller:**
```ts
class UserController extends BaseController {
  async getUser(req, res) {
    const { id } = req.params;
    const user = await this.userService.getById(id);
    return this.ok(res, user);
  }
  
  async createUser(req, res) {
    const data = createUserSchema.parse(req.body);
    const user = await this.userService.create(data);
    return this.created(res, user);
  }
}
```

**Service:**
```ts
class UserService {
  constructor(private userRepo: UserRepository) {}
  
  async getById(id: string) {
    const user = await this.userRepo.findById(id);
    if (!user) throw new AppError('User not found', 404);
    return user;
  }
  
  async create(data: CreateUserDto) {
    // Business logic
    return this.userRepo.create(data);
  }
}
```

**Repository:**
```ts
class UserRepository {
  async findById(id: string) {
    return prisma.user.findUnique({ where: { id } });
  }
  
  async create(data: CreateUserDto) {
    return prisma.user.create({ data });
  }
}
```
