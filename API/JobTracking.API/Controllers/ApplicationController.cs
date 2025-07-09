using JobTracking.App.Contracts;
using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Enums;
using JobTracking.Domain.Filters;
using Microsoft.AspNetCore.Mvc;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Authorization;
using System.Security.Claims;
using System;

namespace JobTracking.API.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ApplicationsController : ControllerBase
{
    private readonly IApplicationService _applicationService;
    private readonly IUserService _userService;

    public ApplicationsController(IApplicationService applicationService, IUserService userService)
    {
        _applicationService = applicationService;
        _userService = userService;
    }

    [HttpPost]
    [Authorize(Roles = "User")]
    public async Task<ActionResult<ApplicationResponseDto>> ApplyForJob([FromBody] ApplicationCreateRequestDto request)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }
        
        var userId = GetCurrentUserId();
        if (userId <= 0)
        {
            // This scenario implies unauthenticated access attempting to apply
            return Unauthorized("User is not authenticated or user ID cannot be determined.");
        }

        try
        {
            var application = await _applicationService.ApplyForJobAsync(userId, request);
            var responseDto = await _applicationService.GetApplicationByIdAsync(application.Id); // Fetch full DTO with job/user names
            return CreatedAtAction(nameof(GetApplication), new { id = responseDto.Id }, responseDto);
        }
        catch (InvalidOperationException ex)
        {
            return Conflict(ex.Message);
        }
        catch (Exception)
        {
            return StatusCode(500, "An error occurred while submitting the application.");
        }
    }

    [HttpGet("{id}")]
    [Authorize(Roles = "Admin,User")]
    public async Task<ActionResult<ApplicationResponseDto>> GetApplication(int id)
    {
        var application = await _applicationService.GetApplicationByIdAsync(id);
        if (application == null)
        {
            return NotFound("Application not found.");
        }

        var currentUserId = GetCurrentUserId();
        if (User.IsInRole(UserRole.User.ToString()) && application.UserId != currentUserId)
        {
            return Forbid("You do not have permission to view this application.");
        }

        return Ok(application);
    }

    [HttpGet]
    [Authorize(Roles = "Admin,User")]
    public async Task<ActionResult<IEnumerable<ApplicationResponseDto>>> GetApplications([FromQuery] ApplicationFilter filter)
    {
        var currentUserId = GetCurrentUserId();
        
        if (User.IsInRole(UserRole.User.ToString()))
        {
            if (filter == null) filter = new ApplicationFilter();
            filter.UserId = currentUserId;
        }

        var applications = await _applicationService.GetApplicationsAsync(filter);
        if (applications == null || !applications.Any())
        {
            return NotFound("No applications found matching the criteria.");
        }

        return Ok(applications);
    }

    [HttpPut("{id}/status")]
    [Authorize(Roles = "Admin")]
    public async Task<IActionResult> UpdateApplicationStatus(int id, [FromBody] ApplicationUpdateRequestDto request)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }

        var success = await _applicationService.UpdateApplicationStatusAsync(id, request.Status);
        if (!success)
        {
            return NotFound("Application not found or update failed.");
        }

        return NoContent();
    }

    [HttpDelete("{id}")]
    [Authorize(Roles = "Admin")]
    public async Task<IActionResult> DeleteApplication(int id)
    {
        var success = await _applicationService.DeleteApplicationAsync(id);
        if (!success)
        {
            return NotFound("Application not found or deletion failed.");
        }

        return NoContent();
    }

    private int GetCurrentUserId()
    {
        var userIdClaim = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
        if (userIdClaim != null && int.TryParse(userIdClaim, out int userId))
        {
            return userId;
        }
        return 0;
    }
}
