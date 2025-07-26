// Controllers/EquipmentController.cs
using Microsoft.AspNetCore.Mvc;

[ApiController]
[Route("api/[controller]")]
public class EquipmentController : ControllerBase
{
    private readonly EquipmentService _equipmentService;

    public EquipmentController(EquipmentService equipmentService)
    {
        _equipmentService = equipmentService;
    }

    [HttpGet]
    public ActionResult<IEnumerable<Equipment>> GetAll()
    {
        return Ok(_equipmentService.GetAll());
    }

    [HttpGet("{id}")]
    public ActionResult<Equipment> GetById(int id)
    {
        var equipment = _equipmentService.GetById(id);
        if (equipment == null) return NotFound();
        return Ok(equipment);
    }

    [HttpPost]
    public ActionResult<Equipment> Create(Equipment equipment)
    {
        var newEquipment = _equipmentService.Add(equipment);
        return CreatedAtAction(nameof(GetById), new { id = newEquipment.Id }, newEquipment);
    }

    [HttpPut("{id}")]
    public IActionResult Update(int id, Equipment equipment)
    {
        var updated = _equipmentService.Update(id, equipment);
        if (updated == null) return NotFound();
        return NoContent();
    }

    [HttpDelete("{id}")]
    public IActionResult Delete(int id)
    {
        if (!_equipmentService.Delete(id)) return NotFound();
        return NoContent();
    }
}

// Controllers/RequestsController.cs
[ApiController]
[Route("api/[controller]")]
public class RequestsController : ControllerBase
{
    private readonly RequestService _requestService;

    public RequestsController(RequestService requestService)
    {
        _requestService = requestService;
    }

    [HttpGet]
    public ActionResult<IEnumerable<EquipmentRequest>> GetAll()
    {
        return Ok(_requestService.GetAll());
    }

    [HttpPost]
    public ActionResult<EquipmentRequest> Create(CreateRequestDto dto)
    {
        var newRequest = _requestService.CreateRequest(dto);
        if (newRequest == null)
        {
            return BadRequest("Item is not available or user/item not found.");
        }
        return Ok(newRequest);
    }

    [HttpPut("{id}/status")]
    public IActionResult UpdateStatus(int id, [FromBody] string status)
    {
        var updatedRequest = _requestService.UpdateRequestStatus(id, status);
        if (updatedRequest == null)
        {
            return NotFound();
        }
        return Ok(updatedRequest);
    }
}

// Controllers/AuthController.cs
[ApiController]
[Route("api/[controller]")]
public class AuthController : ControllerBase
{
    private readonly AuthService _authService;

    public AuthController(AuthService authService)
    {
        _authService = authService;
    }

    [HttpPost("login")]
    public ActionResult<AuthResponse> Login(LoginRequest loginRequest)
    {
        var response = _authService.Authenticate(loginRequest);
        if (response == null)
        {
            return Unauthorized("Invalid credentials.");
        }
        return Ok(response);
    }
}
