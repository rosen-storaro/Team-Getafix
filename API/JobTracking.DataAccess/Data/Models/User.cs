using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using JobTracking.DataAccess.Data.Base;
using JobTracking.Domain.Enums;
using static System.Net.Mime.MediaTypeNames;

namespace JobTracking.DataAccess.Data.Models
{
    public class User : IUser
    {
        public int Id { get; set; }

        [Required]
        [MaxLength(50)]
        public string FirstName { get; set; }

        [MaxLength(50)]
        public string MiddleName { get; set; }

        [Required]
        [MaxLength(50)]
        public string LastName { get; set; }

        [Required]
        [MaxLength(50)]
        public string Username { get; set; }

        [Required]
        [EmailAddress]
        [MaxLength(100)]
        public string Email { get; set; }

        [Required]
        public string PasswordHash { get; set; }

        [Required]
        public UserRole Role { get; set; }

        [MaxLength(200)]
        public string Address { get; set; }
        
        public ICollection<Application> Applications { get; set; }
        
        public ICollection<JobPosting> PostedJobPostings { get; set; }
    }

}
