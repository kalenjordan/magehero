#!/usr/bin/env ruby
# ^ Syntax hint

# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.require_version ">= 1.5.0"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Virtualbox customization
  config.vm.provider :virtualbox do |virtualbox, override|
      # Box configuration
      config.vm.box = "Ubuntu-12.04"
      config.vm.box_url = 'https://cloud-images.ubuntu.com/vagrant/precise/current/precise-server-cloudimg-i386-vagrant-disk1.box'

      # Network configuration
      config.vm.network :private_network, ip: "10.199.45.152"
  end

  # VMWare Fusion customization
  config.vm.provider :vmware_fusion do |vmware, override|
    # Box configuration
    override.vm.box = "debian-wheezy-fusion"
    override.vm.box_url = "http://boxes.monsieurbiz.com/debian-wheezy-fusion.box"

    # Customize VM
    vmware.vmx["memsize"] = "1024"
    vmware.vmx["numvcpus"] = "1"
  end

  # Network
  config.vm.hostname = "magehero.local"
  config.ssh.forward_agent = true

  # Provision
  config.vm.provision :shell, :path => "build/vm_build.sh"

  # Shared folders
  require 'ffi'
  mount_options = ["dmode=777", "fmode=777"]
  opts = if FFI::Platform::IS_WINDOWS
    { :mount_options => mount_options }
  else
    { :nfs => mount_options }
  end

  config.vm.synced_folder(".", "/vagrant", opts)

  # Virtualbox specific configuration
  config.vm.provider :virtualbox do |vb|
    #vb.gui = true
    vb.customize [
      "modifyvm", :id,
      "--memory", "1024",
      "--name", "magehero.local"
    ]
  end

end
