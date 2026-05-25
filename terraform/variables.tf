variable "ssh_key_path" {
  description = "Path to SSH public key"
  type        = string
  default     = "~/.ssh/philasterion.pub"
}

variable "vm_cpus" {
  description = "Number of CPUs"
  type        = number
  default     = 2
}

variable "vm_memory" {
  description = "RAM size"
  type        = string
  default     = "2G"
}

variable "vm_disk" {
  description = "Disk size"
  type        = string
  default     = "10G"
}
