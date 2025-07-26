// =================================================================================
// FILE: SchoolInventory.Identity.Api/Data/IdentityDbContext.cs
// =================================================================================
using Microsoft.EntityFrameworkCore;

namespace SchoolInventory.Identity.Api.Data;

public class IdentityDbContext : DbContext
{
    public IdentityDbContext(DbContextOptions<IdentityDbContext> options) : base(options) { }

    public DbSet<User> Users { get; set; }

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // Seed initial users
        modelBuilder.Entity<User>().HasData(
            new User
            {
                Id = Guid.NewGuid(),
                Username = "admin",
                // In a real app, this hash would be generated from a secure password
                PasswordHash = BCrypt.Net.BCrypt.HashPassword("adminpass"),
                Role = "Admin"
            },
            new User
            {
                Id = Guid.NewGuid(),
                Username = "student",
                PasswordHash = BCrypt.Net.BCrypt.HashPassword("studentpass"),
                Role = "User"
            }
        );
    }
}
