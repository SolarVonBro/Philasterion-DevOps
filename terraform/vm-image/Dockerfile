FROM ubuntu:22.04

ARG SSH_PUBLIC_KEY

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    openssh-server \
    sudo \
    python3 \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir /run/sshd

RUN useradd -m -s /bin/bash ubuntu \
    && echo 'ubuntu ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && mkdir -p /home/ubuntu/.ssh \
    && echo "${SSH_PUBLIC_KEY}" > /home/ubuntu/.ssh/authorized_keys \
    && chown -R ubuntu:ubuntu /home/ubuntu/.ssh \
    && chmod 700 /home/ubuntu/.ssh \
    && chmod 600 /home/ubuntu/.ssh/authorized_keys

EXPOSE 22
CMD ["/usr/sbin/sshd", "-D"]