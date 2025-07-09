using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Filters;

namespace JobTracking.App.Contracts;

public interface IJobService
{ 
    Task<List<JobResponseDto>> GetJobsAsync(int page, int pageSize, JobFilter filter = null);
    Task<JobResponseDto?> GetJobAsync(int jobId);
    Task<JobResponseDto> CreateJobAsync(JobRequestDto dto);
    Task<bool> UpdateJobAsync(int jobId, JobRequestDto dto);
    Task<bool> DeleteJobAsync(int jobId);
}