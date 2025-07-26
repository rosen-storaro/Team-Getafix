// Services/EquipmentService.cs
public class EquipmentService
{
    private readonly InMemoryDataStore _store;

    public EquipmentService(InMemoryDataStore store)
    {
        _store = store;
    }

    public IEnumerable<Equipment> GetAll() => _store.Equipment.Values;

    public Equipment? GetById(int id) => _store.Equipment.GetValueOrDefault(id);

    public Equipment Add(Equipment equipment)
    {
        equipment.Id = _store.GetNextEquipmentId();
        _store.Equipment.TryAdd(equipment.Id, equipment);
        return equipment;
    }

    public Equipment? Update(int id, Equipment updatedEquipment)
    {
        if (_store.Equipment.TryGetValue(id, out var existingEquipment))
        {
            existingEquipment.Name = updatedEquipment.Name;
            existingEquipment.Type = updatedEquipment.Type;
            existingEquipment.SerialNumber = updatedEquipment.SerialNumber;
            existingEquipment.Condition = updatedEquipment.Condition;
            existingEquipment.Status = updatedEquipment.Status;
            existingEquipment.Location = updatedEquipment.Location;
            return existingEquipment;
        }
        return null;
    }

    public bool Delete(int id) => _store.Equipment.TryRemove(id, out _);
}

// Services/RequestService.cs
public class RequestService
{
    private readonly InMemoryDataStore _store;

    public RequestService(InMemoryDataStore store)
    {
        _store = store;
    }

    public IEnumerable<EquipmentRequest> GetAll() => _store.Requests.Values;

    public EquipmentRequest? CreateRequest(CreateRequestDto dto)
    {
        if (!_store.Equipment.TryGetValue(dto.EquipmentId, out var equipment) ||
            !_store.Users.TryGetValue(dto.UserId, out var user))
        {
            return null; // Equipment or User not found
        }
        
        if (equipment.Status != "Available")
        {
            return null; // Item not available
        }

        var newRequest = new EquipmentRequest
        {
            Id = _store.GetNextRequestId(),
            EquipmentId = equipment.Id,
            EquipmentName = equipment.Name,
            UserId = user.Id,
            UserName = user.Username,
            Status = "Pending"
        };

        _store.Requests.TryAdd(newRequest.Id, newRequest);
        return newRequest;
    }

    public EquipmentRequest? UpdateRequestStatus(int requestId, string status)
    {
        if (!_store.Requests.TryGetValue(requestId, out var request))
        {
            return null;
        }

        request.Status = status;

        // If approved, update the equipment status
        if (status == "Approved")
        {
            if (_store.Equipment.TryGetValue(request.EquipmentId, out var equipment))
            {
                equipment.Status = "Checked Out";
            }
        }
        // If returned, update the equipment status
        else if (status == "Returned")
        {
             if (_store.Equipment.TryGetValue(request.EquipmentId, out var equipment))
            {
                equipment.Status = "Available";
            }
        }

        return request;
    }
}

// Services/AuthService.cs
public class AuthService
{
    private readonly InMemoryDataStore _store;

    public AuthService(InMemoryDataStore store)
    {
        _store = store;
    }

    public AuthResponse? Authenticate(LoginRequest login)
    {
        var user = _store.Users.Values.FirstOrDefault(u => u.Username.Equals(login.Username, StringComparison.OrdinalIgnoreCase));

        // IMPORTANT: This is a mock authentication. In a real app, use a secure password hashing and verification library.
        if (user != null && user.PasswordHash == login.Password)
        {
            return new AuthResponse
            {
                UserId = user.Id,
                Username = user.Username,
                Role = user.Role,
                Token = $"fake-jwt-token-for-{user.Username}" // Generate a real JWT in a production app.
            };
        }
        return null;
    }
}
