// Models/Equipment.cs
public class Equipment
{
    public int Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Type { get; set; } = string.Empty;
    public string SerialNumber { get; set; } = string.Empty;
    public string Condition { get; set; } = string.Empty;
    public string Status { get; set; } = "Available"; // e.g., Available, Checked Out, Under Repair
    public string Location { get; set; } = string.Empty;
    public string? PhotoUrl { get; set; }
}

// Models/EquipmentRequest.cs
public class EquipmentRequest
{
    public int Id { get; set; }
    public int EquipmentId { get; set; }
    public string EquipmentName { get; set; } = string.Empty;
    public int UserId { get; set; }
    public string UserName { get; set; } = string.Empty;
    public DateTime RequestDate { get; set; } = DateTime.UtcNow;
    public string Status { get; set; } = "Pending"; // e.g., Pending, Approved, Rejected, Returned
}

// Models/User.cs
public class User
{
    public int Id { get; set; }
    public string Username { get; set; } = string.Empty;
    public string Role { get; set; } = "User"; // "User" or "Admin"
    public string PasswordHash { get; set; } = string.Empty; // In a real app, NEVER store plain text passwords.
}

// DTOs (Data Transfer Objects) for API communication

public class LoginRequest
{
    public string Username { get; set; } = string.Empty;
    public string Password { get; set; } = string.Empty;
}

public class AuthResponse
{
    public int UserId { get; set; }
    public string Username { get; set; } = string.Empty;
    public string Role { get; set; } = string.Empty;
    public string Token { get; set; } = string.Empty; // JWT token
}

public class CreateRequestDto
{
    public int EquipmentId { get; set; }
    public int UserId { get; set; }
}
