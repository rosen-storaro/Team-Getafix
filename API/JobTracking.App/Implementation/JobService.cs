using System.Diagnostics;
using JobTracking.App.Contracts;
using JobTracking.App.Contracts.Base;
using JobTracking.DataAccess;
using JobTracking.DataAccess.Data.Models;
using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Filters; // Ensure this using is correct for JobFilter
using JobTracking.Domain.Enums; // Ensure this using is correct for JobStatus enum
using JobTracking.Domain.Filters.Base;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Query.Internal;
using Microsoft.Extensions.Logging;

namespace JobTracking.App.Implementation;

public class JobService : IJobService
{
    private readonly ApplicationDbContext _context;
    
    public JobService(ApplicationDbContext context) // <--- FINAL CHANGE: Constructor takes DbContext directly
    {
        _context = context;
    }

    public async Task<List<JobResponseDto>> GetJobsAsync(int page, int pageSize, JobFilter filter = null)
    {
        IQueryable<JobPosting> query = _context.JobPostings;

        if (filter != null)
        {
            if (!string.IsNullOrEmpty(filter.Title))
            {
                query = query.Where(j => j.Title.ToLower().Contains(filter.Title.ToLower()));
            }
            if (!string.IsNullOrEmpty(filter.CompanyName))
            {
                query = query.Where(j => j.CompanyName.ToLower().Contains(filter.CompanyName.ToLower()));
            }
            if (!string.IsNullOrEmpty(filter.Description))
            {
                query = query.Where(j => j.Description.ToLower().Contains(filter.Description.ToLower()));
            }
            
            if (filter.Status.HasValue)
            {
                string filterStatusString = filter.Status.Value.ToString().ToLower();
                query = query.Where(j => j.Status.ToLower() == filterStatusString);
            }
        }

        return await query
            .Skip((page - 1) * pageSize)
            .Take(pageSize)
            .Select(j => new JobResponseDto
            {
                Id = j.Id,
                Title = j.Title,
                CompanyName = j.CompanyName,
                Description = j.Description,
                DatePosted = j.DatePosted,
                Status = j.Status
            })
            .ToListAsync();
    }

    public async Task<JobResponseDto?> GetJobAsync(int jobId)
    {
        return await _context.JobPostings
            .Where(j => j.Id == jobId)
            .Select(j => new JobResponseDto
            {
                Id = j.Id,
                Title = j.Title,
                CompanyName = j.CompanyName,
                Description = j.Description,
                DatePosted = j.DatePosted,
                Status = j.Status
            })
            .FirstOrDefaultAsync();
    }

    public async Task<JobResponseDto> CreateJobAsync(JobRequestDto dto)
    {
        var job = new JobPosting
        {
            Title = dto.Title,
            CompanyName = dto.CompanyName,
            Description = dto.Description,
            DatePosted = DateTime.UtcNow,
            Status = dto.Status,
            UserId = dto.UserId
        };

        _context.JobPostings.Add(job);
        await _context.SaveChangesAsync();

        return new JobResponseDto
        {
            Id = job.Id,
            Title = job.Title,
            CompanyName = job.CompanyName,
            Description = job.Description,
            DatePosted = job.DatePosted,
            Status = job.Status
        };
    }

    public async Task<bool> UpdateJobAsync(int jobId, JobRequestDto dto)
    {
        var job = await _context.JobPostings.FindAsync(jobId);
        if (job == null)
            return false;

        job.Title = dto.Title;
        job.CompanyName = dto.CompanyName;
        job.Description = dto.Description;
        job.Status = dto.Status.ToString(); // Convert enum to string for storage
        job.UserId = dto.UserId;

        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> DeleteJobAsync(int jobId)
    {
        var job = await _context.JobPostings.FindAsync(jobId);
        if (job == null)
            return false;

        _context.JobPostings.Remove(job);
        await _context.SaveChangesAsync();
        return true;
    }
}