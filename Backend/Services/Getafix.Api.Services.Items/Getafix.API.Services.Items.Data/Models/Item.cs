using Getafix.Api.Services.Shared.Data.Interfaces;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Getafix.Api.Services.Items.Data.Models;

/// <summary>
/// Represents an item in the system.
/// </summary>
public class Item : ISoftDelete
{
    public int Id { get; set; }

    [Required]
    [MaxLength(200)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(1000)]
    public string? Description { get; set; }

    [Required]
    public ItemType Type { get; set; }

    [MaxLength(500)]
    public string? ImageUrl { get; set; }

    [Required]
    [MaxLength(200)]
    public string Location { get; set; } = string.Empty;

    [Required]
    public ItemStatus Status { get; set; } = ItemStatus.Available;

    [Required]
    [MaxLength(200)]
    public string StorageLocation { get; set; } = string.Empty;

    [MaxLength(100)]
    public string? SerialNumber { get; set; }

    [Required]
    public ItemCondition Condition { get; set; } = ItemCondition.Good;

    // For countable items (consumables)
    public int? Quantity { get; set; }
    public int? MinimumThreshold { get; set; }

    // For non-countable items tracking
    public bool IsAvailable { get; set; } = true;

    [Required]
    public int CategoryId { get; set; }

    public bool IsDeleted { get; set; } = false;

    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

    public DateTime? UpdatedAt { get; set; }

    // Navigation property
    [ForeignKey("CategoryId")]
    public virtual Category Category { get; set; } = null!;
}