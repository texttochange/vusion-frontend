Vagrant::Config.run do |config|

  config.vm.box = "lucid32"
  config.vm.box_url = "http://files.vagrantup.com/lucid32.box"

  config.vm.provision :puppet do |puppet|
    puppet.options = ["--verbose",  "--pluginsync", "--debug"]
    puppet.manifests_path = "puppet/manifests"
    puppet.manifest_file = "vusion.pp"
    puppet.module_path = "/home/oliv/.puppet/modules"
  end

  config.vm.forward_port 80, 4567

end
