output "public_ip" {
  description = "IP address of the VM"
  value       = multipass_instance.philasterion_vm.ipv4
}

output "vm_connect" {
  description = "SSH command to connect to the VM"
  value       = "ssh -i ~/.ssh/philasterion ubuntu@${multipass_instance.philasterion_vm.ipv4}"
}
