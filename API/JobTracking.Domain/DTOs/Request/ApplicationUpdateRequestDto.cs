namespace JobTracking.Domain.DTOs.Request;

using System.ComponentModel.DataAnnotations;

public class ApplicationUpdateRequestDto
{
    [Required]
    [MaxLength(50)]
    public string Status { get; set; }
}
