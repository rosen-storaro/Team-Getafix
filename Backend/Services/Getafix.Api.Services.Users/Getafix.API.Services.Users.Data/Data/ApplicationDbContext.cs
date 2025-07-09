using Getafix.Api.Services.Users.Data.Models.Identity;
using Microsoft.AspNetCore.Identity.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore;

namespace Getafix.Api.Services.Users.Data.Data;

/// <summary>
/// Application Database Context.
/// </summary>
/// <param name="options">Database Context Options.</param>
public class ApplicationDbContext(DbContextOptions<ApplicationDbContext> options) 
    : IdentityDbContext<User>(options)
{
    /// <summary>
    /// Gets or sets RefreshTokens.
    /// </summary>
    public virtual DbSet<RefreshToken> RefreshTokens { get; set; }
    
    /// <summary>
    /// Overrides the default on model creating method.
    /// </summary>
    /// <param name="builder">Model Builder.</param>
    protected override void OnModelCreating(ModelBuilder builder)
    {
        builder.Entity<User>()
            .HasQueryFilter(x => x.IsDeleted == false);
        
        base.OnModelCreating(builder);
    }
}