using System.Net.Mime;
using JobTracking.DataAccess.Data.Models;
using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Filters;

namespace JobTracking.App.Contracts;

public interface IApplicationService
{
    Task<Application> ApplyForJobAsync(int userId, ApplicationCreateRequestDto request);
    Task<ApplicationResponseDto> GetApplicationByIdAsync(int applicationId);
    Task<IEnumerable<ApplicationResponseDto>> GetApplicationsAsync(ApplicationFilter filter);
    Task<bool> UpdateApplicationStatusAsync(int applicationId, string newStatus);
    Task<bool> DeleteApplicationAsync(int applicationId);
    Task<bool> HasUserAppliedAsync(int userId, int jobPostingId);
}