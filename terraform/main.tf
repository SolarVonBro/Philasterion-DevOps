terraform {
  required_providers {
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 3.0"
    }
  }
}

provider "docker" {}

resource "docker_image" "vm_image" {
  name = "philasterion-vm:latest"

  build {
    context    = "${path.module}/vm-image"
    dockerfile = "Dockerfile"
    build_args = {
      SSH_PUBLIC_KEY = trimspace(file(pathexpand("~/.ssh/philasterion.pub")))
    }
    force_remove = true
  }

  triggers = {
    dockerfile = filesha256("${path.module}/vm-image/Dockerfile")
  }
}

resource "docker_container" "philasterion_vm" {
  image      = docker_image.vm_image.image_id
  name       = "philasterion-vm"
  restart    = "unless-stopped"
  privileged = true

  ports {
    internal = 22
    external = 2222
  }
}

output "vm_ssh_host" {
  value = "localhost"
}

output "vm_ssh_port" {
  value = 2222
}

output "vm_connect" {
  value = "ssh -i ~/.ssh/philasterion -p 2222 ubuntu@localhost"
}