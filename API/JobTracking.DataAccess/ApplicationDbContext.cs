using JobTracking.DataAccess.Data.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;

namespace JobTracking.DataAccess;

public class ApplicationDbContext : DbContext
{
    
    public DbSet<User> Users { get; set; }
    public DbSet<Application> Applications { get; set; }
    public DbSet<JobPosting> JobPostings { get; set; }

    public ApplicationDbContext(DbContextOptions<ApplicationDbContext> options)
        : base(options)
    {
        
    }
    
    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);
        
        modelBuilder.Entity<User>()
            .HasMany(u => u.Applications)
            .WithOne(a => a.User)
            .HasForeignKey(a => a.UserId);
        
        modelBuilder.Entity<User>()
            .HasMany(u => u.PostedJobPostings)
            .WithOne(jp => jp.User)
            .HasForeignKey(jp => jp.UserId)
            .OnDelete(DeleteBehavior.Restrict);
        
        modelBuilder.Entity<JobPosting>()
            .HasMany(jp => jp.Applications)
            .WithOne(a => a.JobPosting)
            .HasForeignKey(a => a.JobPostingId);
        
        modelBuilder.Entity<User>()
            .HasIndex(u => u.Username)
            .IsUnique();

        modelBuilder.Entity<User>()
            .HasIndex(u => u.Email)
            .IsUnique();
    }
}