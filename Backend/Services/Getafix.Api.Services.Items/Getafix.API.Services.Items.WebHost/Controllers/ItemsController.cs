using AutoMapper;
using Getafix.Api.Services.Items.Data.Models;
using Getafix.Api.Services.Items.Services.Contracts;
using Getafix.Api.Services.Items.Shared.DTOs;
using Getafix.Api.Services.Items.Shared.Models;
using Microsoft.AspNetCore.Mvc;

namespace Getafix.Api.Services.Items.WebHost.Controllers;

/// <summary>
/// Controller for managing items.
/// </summary>
[ApiController]
[Route("api/[controller]")]
public class ItemsController : ControllerBase
{
    private readonly IItemService _itemService;
    private readonly IMapper _mapper;

    /// <summary>
    /// Initializes a new instance of the <see cref="ItemsController"/> class.
    /// </summary>
    /// <param name="itemService">The item service.</param>
    /// <param name="mapper">The AutoMapper instance.</param>
    public ItemsController(IItemService itemService, IMapper mapper)
    {
        _itemService = itemService;
        _mapper = mapper;
    }

    /// <summary>
    /// Gets all items.
    /// </summary>
    /// <returns>A collection of items.</returns>
    [HttpGet]
    public async Task<ActionResult<IEnumerable<ItemDto>>> GetItems()
    {
        var items = await _itemService.GetAllItemsAsync();
        var itemDtos = _mapper.Map<IEnumerable<ItemDto>>(items);
        return Ok(itemDtos);
    }

    /// <summary>
    /// Gets an item by its identifier.
    /// </summary>
    /// <param name="id">The item identifier.</param>
    /// <returns>The item if found.</returns>
    [HttpGet("{id}")]
    public async Task<ActionResult<ItemDto>> GetItem(int id)
    {
        var item = await _itemService.GetItemByIdAsync(id);
        if (item == null)
        {
            return NotFound(new ResponseModel { Status = "Error", Message = "Item not found" });
        }

        var itemDto = _mapper.Map<ItemDto>(item);
        return Ok(itemDto);
    }

    /// <summary>
    /// Creates a new item.
    /// </summary>
    /// <param name="createItemDto">The item creation data.</param>
    /// <returns>The created item.</returns>
    [HttpPost]
    public async Task<ActionResult<ItemDto>> CreateItem([FromBody] CreateItemDto createItemDto)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }

        var item = _mapper.Map<Item>(createItemDto);
        var createdItem = await _itemService.CreateItemAsync(item);
        var itemDto = _mapper.Map<ItemDto>(createdItem);

        return CreatedAtAction(nameof(GetItem), new { id = itemDto.Id }, itemDto);
    }

    /// <summary>
    /// Updates an existing item.
    /// </summary>
    /// <param name="id">The item identifier.</param>
    /// <param name="updateItemDto">The item update data.</param>
    /// <returns>The updated item.</returns>
    [HttpPut("{id}")]
    public async Task<ActionResult<ItemDto>> UpdateItem(int id, [FromBody] UpdateItemDto updateItemDto)
    {
        if (id != updateItemDto.Id)
        {
            return BadRequest(new ResponseModel { Status = "Error", Message = "ID mismatch" });
        }

        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }

        var existingItem = await _itemService.GetItemByIdAsync(id);
        if (existingItem == null)
        {
            return NotFound(new ResponseModel { Status = "Error", Message = "Item not found" });
        }

        var item = _mapper.Map<Item>(updateItemDto);
        var updatedItem = await _itemService.UpdateItemAsync(item);
        var itemDto = _mapper.Map<ItemDto>(updatedItem);

        return Ok(itemDto);
    }

    /// <summary>
    /// Deletes an item by its identifier.
    /// </summary>
    /// <param name="id">The item identifier.</param>
    /// <returns>A response indicating success or failure.</returns>
    [HttpDelete("{id}")]
    public async Task<ActionResult<ResponseModel>> DeleteItem(int id)
    {
        var result = await _itemService.DeleteItemAsync(id);
        if (!result)
        {
            return NotFound(new ResponseModel { Status = "Error", Message = "Item not found" });
        }

        return Ok(new ResponseModel { Status = "Success", Message = "Item deleted successfully" });
    }

    /// <summary>
    /// Gets items by category.
    /// </summary>
    /// <param name="category">The category to filter by.</param>
    /// <returns>A collection of items in the specified category.</returns>
    [HttpGet("category/{category}")]
    public async Task<ActionResult<IEnumerable<ItemDto>>> GetItemsByCategory(string category)
    {
        var items = await _itemService.GetItemsByCategoryAsync(category);
        var itemDtos = _mapper.Map<IEnumerable<ItemDto>>(items);
        return Ok(itemDtos);
    }
}
