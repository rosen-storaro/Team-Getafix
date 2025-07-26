using Getafix.Api.Services.Items.Data.Models;
using Microsoft.EntityFrameworkCore;

namespace Getafix.Api.Services.Items.Data.Data;

/// <summary>
/// Application Database Context.
/// </summary>
/// <param name="options">Database Context Options.</param>
public class ApplicationDbContext(DbContextOptions<ApplicationDbContext> options) 
    : DbContext(options)
{
    /// <summary>
    /// Gets or sets Items.
    /// </summary>
    public virtual DbSet<Item> Items { get; set; }
    
    /// <summary>
    /// Overrides the default on model creating method.
    /// </summary>
    /// <param name="builder">Model Builder.</param>
    protected override void OnModelCreating(ModelBuilder builder)
    {
        builder.Entity<Item>()
            .HasQueryFilter(x => x.IsDeleted == false);
        
        base.OnModelCreating(builder);
    }
}
