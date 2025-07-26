using System.ComponentModel.DataAnnotations;

namespace Getafix.Api.Services.Items.Shared.DTOs;

/// <summary>
/// Data transfer object for creating an item.
/// </summary>
public class CreateItemDto
{
    /// <summary>
    /// Gets or sets the name of the item.
    /// </summary>
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets the description of the item.
    /// </summary>
    [MaxLength(500)]
    public string? Description { get; set; }

    /// <summary>
    /// Gets or sets the price of the item.
    /// </summary>
    [Range(0, double.MaxValue, ErrorMessage = "Price must be greater than or equal to 0")]
    public decimal Price { get; set; }

    /// <summary>
    /// Gets or sets the category of the item.
    /// </summary>
    [MaxLength(50)]
    public string? Category { get; set; }

    /// <summary>
    /// Gets or sets the quantity in stock.
    /// </summary>
    [Range(0, int.MaxValue, ErrorMessage = "Stock must be greater than or equal to 0")]
    public int Stock { get; set; }
}
