// Data/InMemoryDataStore.cs
// This class simulates a database using in-memory collections.
// In a real application, this would be replaced with a database context (e.g., Entity Framework Core).

using System.Collections.Concurrent;

public class InMemoryDataStore
{
    // Using ConcurrentDictionary for thread-safe access to the data.
    public ConcurrentDictionary<int, Equipment> Equipment { get; } = new();
    public ConcurrentDictionary<int, EquipmentRequest> Requests { get; } = new();
    public ConcurrentDictionary<int, User> Users { get; } = new();

    private int _nextEquipmentId = 1;
    private int _nextRequestId = 1;
    private int _nextUserId = 1;

    public InMemoryDataStore()
    {
        // Pre-populate with some sample data to make the application usable on startup.
        // Add sample users
        AddUser(new User { Username = "admin", Role = "Admin", PasswordHash = "adminpass" }); // In a real app, hash passwords!
        AddUser(new User { Username = "student1", Role = "User", PasswordHash = "studentpass" });

        // Add sample equipment
        AddEquipment(new Equipment { Name = "Laptop A", Type = "Laptop", SerialNumber = "SN001", Condition = "Good", Status = "Available", Location = "Room 101" });
        AddEquipment(new Equipment { Name = "Projector B", Type = "Projector", SerialNumber = "SN002", Condition = "Excellent", Status = "Available", Location = "Library" });
        AddEquipment(new Equipment { Name = "USB Drive 16GB", Type = "Storage", SerialNumber = "SN003", Condition = "Good", Status = "Checked Out", Location = "N/A" });
        AddEquipment(new Equipment { Name = "Whiteboard Markers", Type = "Supplies", SerialNumber = "SN004", Condition = "New", Status = "Available", Location = "Storage Closet" });
    }

    // Helper methods to manage IDs
    public int GetNextEquipmentId() => Interlocked.Increment(ref _nextEquipmentId);
    public int GetNextRequestId() => Interlocked.Increment(ref _nextRequestId);
    public int GetNextUserId() => Interlocked.Increment(ref _nextUserId);

    private void AddUser(User user)
    {
        user.Id = GetNextUserId();
        Users.TryAdd(user.Id, user);
    }

    private void AddEquipment(Equipment equipment)
    {
        equipment.Id = GetNextEquipmentId();
        Equipment.TryAdd(equipment.Id, equipment);
    }
}
