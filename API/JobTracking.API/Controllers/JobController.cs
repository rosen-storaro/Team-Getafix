using JobTracking.App.Contracts;
using JobTracking.App.Implementation;
using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;

namespace JobTracking.API.Controllers;
using Microsoft.AspNetCore.Mvc;

[ApiController]
[Route("api/[controller]")]
public class JobController : ControllerBase
{
    private readonly IJobService _jobService;

    public JobController(IJobService jobService)
    {
        _jobService = jobService;
    }
    
    [HttpGet]
    public async Task<ActionResult<List<JobResponseDto>>> GetJobs([FromQuery] int page = 1, [FromQuery] int pageSize = 10)
    {
        var jobs = await _jobService.GetJobsAsync(page, pageSize);
        return Ok(jobs);
    }
    
    [HttpGet("{id}")]
    public async Task<ActionResult<JobResponseDto>> GetJob(int id)
    {
        var job = await _jobService.GetJobAsync(id);
        if (job == null)
            return NotFound();

        return Ok(job);
    }
    
    [HttpPost]
    public async Task<ActionResult<JobResponseDto>> CreateJob([FromBody] JobRequestDto dto)
    {
        var createdJob = await _jobService.CreateJobAsync(dto);
        return CreatedAtAction(nameof(GetJob), new { id = createdJob.Id }, createdJob);
    }
    
    [HttpPut("{id}")]
    public async Task<IActionResult> UpdateJob(int id, [FromBody] JobRequestDto dto)
    {
        var success = await _jobService.UpdateJobAsync(id, dto);
        if (!success)
            return NotFound();

        return NoContent();
    }
    
    [HttpDelete("{id}")]
    public async Task<IActionResult> DeleteJob(int id)
    {
        var success = await _jobService.DeleteJobAsync(id);
        if (!success)
            return NotFound();

        return NoContent();
    }
}