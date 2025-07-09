using JobTracking.App.Contracts;
using JobTracking.DataAccess;
using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Filters;
using Microsoft.EntityFrameworkCore;
using JobTracking.DataAccess.Data.Models; // Assuming this is correct for your models

namespace JobTracking.App.Implementation; // Adjust namespace if different

public class ApplicationService : IApplicationService
{
    private readonly ApplicationDbContext _context;

    public ApplicationService(ApplicationDbContext context)
    {
        _context = context;
    }

    public async Task<Application> ApplyForJobAsync(int userId, ApplicationCreateRequestDto request)
    {
        var jobPostingExists = await _context.JobPostings.AnyAsync(jp => jp.Id == request.JobPostingId);
        if (!jobPostingExists)
        {
            throw new InvalidOperationException("Job posting not found.");
        }

        var hasApplied = await HasUserAppliedAsync(userId, request.JobPostingId);
        if (hasApplied)
        {
            throw new InvalidOperationException("User has already applied to this job posting.");
        }

        var application = new Application
        {
            UserId = userId,
            JobPostingId = request.JobPostingId,
            CoverLetter = request.CoverLetter,
            ApplicationDate = DateTime.UtcNow,
            Status = "Pending"
        };

        _context.Applications.Add(application);
        await _context.SaveChangesAsync();
        return application;
    }

    public async Task<ApplicationResponseDto> GetApplicationByIdAsync(int applicationId)
    {
        var application = await _context.Applications
            .Include(a => a.User)
            .Include(a => a.JobPosting)
            .FirstOrDefaultAsync(a => a.Id == applicationId);

        if (application == null) return null;

        return new ApplicationResponseDto
        {
            Id = application.Id,
            UserId = application.UserId,
            UserName = application.User.Username,
            JobPostingId = application.JobPostingId,
            JobTitle = application.JobPosting.Title,
            ApplicationDate = application.ApplicationDate,
            Status = application.Status,
            CoverLetter = application.CoverLetter
        };
    }

    public async Task<IEnumerable<ApplicationResponseDto>> GetApplicationsAsync(ApplicationFilter filter)
    {
        IQueryable<Application> query = _context.Applications
            .Include(a => a.User)
            .Include(a => a.JobPosting);

        if (filter != null)
        {
            if (filter.UserId.HasValue)
            {
                query = query.Where(a => a.UserId == filter.UserId.Value);
            }
            if (filter.JobPostingId.HasValue)
            {
                query = query.Where(a => a.JobPostingId == filter.JobPostingId.Value);
            }
            if (!string.IsNullOrEmpty(filter.Status))
            {
                // FIX: Changed to ToLower() for translatability
                query = query.Where(a => a.Status.ToLower().Contains(filter.Status.ToLower()));
            }
        }

        var applications = await query.ToListAsync();

        return applications.Select(application => new ApplicationResponseDto
        {
            Id = application.Id,
            UserId = application.UserId,
            UserName = application.User.Username,
            JobPostingId = application.JobPostingId,
            JobTitle = application.JobPosting.Title,
            ApplicationDate = application.ApplicationDate,
            Status = application.Status,
            CoverLetter = application.CoverLetter
        }).ToList();
    }

    public async Task<bool> UpdateApplicationStatusAsync(int applicationId, string newStatus)
    {
        var application = await _context.Applications.FindAsync(applicationId);
        if (application == null) return false;

        application.Status = newStatus;
        _context.Entry(application).State = EntityState.Modified;

        try
        {
            await _context.SaveChangesAsync();
            return true;
        }
        catch (DbUpdateConcurrencyException)
        {
            return false;
        }
    }

    public async Task<bool> DeleteApplicationAsync(int applicationId)
    {
        var application = await _context.Applications.FindAsync(applicationId);
        if (application == null) return false;

        _context.Applications.Remove(application);
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> HasUserAppliedAsync(int userId, int jobPostingId)
    {
        return await _context.Applications.AnyAsync(a => a.UserId == userId && a.JobPostingId == jobPostingId);
    }
}