Vagrant::Config.run do |config|

  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.provision :puppet do |puppet|
    puppet.options = ["--verbose",  "--pluginsync"]
    puppet.manifests_path = "puppet/manifests"
    puppet.manifest_file = "vusion.pp"
    puppet.module_path = "/home/oliv/.puppet/modules"
  end

  config.vm.forward_port 80, 4567   
  config.vm.forward_port 9010, 4568

  config.vm.network :hostonly, "10.11.12.13"
  #config.vm.share_folder('frontend', '/var/vusion/vusion-frontend/app', 'app', :nfs => true)
  #config.vm.share_folder('backend', '/var/vusion/vusion-backend/vusion', '/home/oliv/Development/vusion-backend/vusion', :nfs => true)

end
