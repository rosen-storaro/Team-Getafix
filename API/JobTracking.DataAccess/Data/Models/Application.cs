using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using JobTracking.DataAccess.Data.Base;

namespace JobTracking.DataAccess.Data.Models
{
    public class Application
    {
        public int Id { get; set; }

        [Required]
        public int UserId { get; set; }

        [ForeignKey("UserId")]
        public User User { get; set; }

        [Required]
        public int JobPostingId { get; set; }

        [ForeignKey("JobPostingId")]
        public JobPosting JobPosting { get; set; }

        public DateTime ApplicationDate { get; set; } = DateTime.UtcNow;

        [MaxLength(50)]
        public string Status { get; set; } = "Pending";

        [MaxLength(2000)]
        public string CoverLetter { get; set; }
    }
}