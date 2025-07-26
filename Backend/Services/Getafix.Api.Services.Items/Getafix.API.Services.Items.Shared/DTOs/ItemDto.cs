namespace Getafix.Api.Services.Items.Shared.DTOs;

/// <summary>
/// Data transfer object for item response.
/// </summary>
public class ItemDto
{
    /// <summary>
    /// Gets or sets the unique identifier for the item.
    /// </summary>
    public int Id { get; set; }

    /// <summary>
    /// Gets or sets the name of the item.
    /// </summary>
    public string Name { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets the description of the item.
    /// </summary>
    public string? Description { get; set; }

    /// <summary>
    /// Gets or sets the price of the item.
    /// </summary>
    public decimal Price { get; set; }

    /// <summary>
    /// Gets or sets the category of the item.
    /// </summary>
    public string? Category { get; set; }

    /// <summary>
    /// Gets or sets the quantity in stock.
    /// </summary>
    public int Stock { get; set; }

    /// <summary>
    /// Gets or sets when the item was created.
    /// </summary>
    public DateTime CreatedAt { get; set; }

    /// <summary>
    /// Gets or sets when the item was last updated.
    /// </summary>
    public DateTime UpdatedAt { get; set; }
}
