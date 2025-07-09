using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using JobTracking.DataAccess.Data.Base;
using static System.Net.Mime.MediaTypeNames;
using JobTracking.Domain.Enums;

namespace JobTracking.DataAccess.Data.Models
{
    public class JobPosting
    {
        public int Id { get; set; }

        [Required]
        [MaxLength(100)]
        public string Title { get; set; }

        [Required]
        [MaxLength(2000)]
        public string Description { get; set; }

        [Required]
        [MaxLength(100)]
        public string CompanyName { get; set; }

        [Required]
        public int UserId { get; set; }

        [ForeignKey("UserId")]
        public User User { get; set; }
        
        public DateTime DatePosted { get; set; } = DateTime.UtcNow;
        

        [Required]
        [MaxLength(50)]
        public string Status { get; set; } = "Open";
        public ICollection<Application> Applications { get; set; }
    }
}
