terraform {
  required_providers {
    multipass = {
      source  = "larstobi/multipass"
      version = "~> 1.4"
    }
  }
}

provider "multipass" {}

resource "local_file" "cloud_init" {
  content = templatefile("${path.module}/cloud-init.tpl", {
    ssh_public_key = trimspace(file(pathexpand(var.ssh_key_path)))
  })
  filename = "${path.module}/cloud-init.yml"
}

resource "multipass_instance" "philasterion_vm" {
  name           = "philasterion-vm"
  image          = "22.04"
  cpus           = 2
  memory         = "2G"
  disk           = "10G"
  cloudinit_file = local_file.cloud_init.filename

  depends_on = [local_file.cloud_init]
}

resource "local_file" "ansible_inventory" {
  content  = <<-EOT
    [vm]
    philasterion-vm ansible_host=${multipass_instance.philasterion_vm.ipv4} ansible_user=ubuntu ansible_ssh_private_key_file=~/.ssh/philasterion ansible_ssh_common_args='-o StrictHostKeyChecking=no'
  EOT
  filename = "${path.module}/../ansible/inventory.ini"
}
