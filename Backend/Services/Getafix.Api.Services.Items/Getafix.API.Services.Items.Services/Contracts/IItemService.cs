using Getafix.Api.Services.Items.Data.Models;

namespace Getafix.Api.Services.Items.Services.Contracts;

/// <summary>
/// Interface for item service operations.
/// </summary>
public interface IItemService
{
    /// <summary>
    /// Gets all items.
    /// </summary>
    /// <returns>A collection of items.</returns>
    Task<IEnumerable<Item>> GetAllItemsAsync();

    /// <summary>
    /// Gets an item by its identifier.
    /// </summary>
    /// <param name="id">The item identifier.</param>
    /// <returns>The item if found, otherwise null.</returns>
    Task<Item?> GetItemByIdAsync(int id);

    /// <summary>
    /// Creates a new item.
    /// </summary>
    /// <param name="item">The item to create.</param>
    /// <returns>The created item.</returns>
    Task<Item> CreateItemAsync(Item item);

    /// <summary>
    /// Updates an existing item.
    /// </summary>
    /// <param name="item">The item to update.</param>
    /// <returns>The updated item.</returns>
    Task<Item> UpdateItemAsync(Item item);

    /// <summary>
    /// Deletes an item by its identifier.
    /// </summary>
    /// <param name="id">The item identifier.</param>
    /// <returns>True if the item was deleted, otherwise false.</returns>
    Task<bool> DeleteItemAsync(int id);

    /// <summary>
    /// Gets items by category.
    /// </summary>
    /// <param name="category">The category to filter by.</param>
    /// <returns>A collection of items in the specified category.</returns>
    Task<IEnumerable<Item>> GetItemsByCategoryAsync(string category);
}
