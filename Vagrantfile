Vagrant::Config.run do |config|

  config.vm.box = "package"
  config.vm.box_url = "file:///Users/olivier/Development/vusion/package.box"

  #config.vm.provision :puppet do |puppet|
  #  puppet.options = ["--verbose",  "--pluginsync"]
  #  puppet.manifests_path = "puppet/manifests"
  #  puppet.manifest_file = "vusion.pp"
  #  puppet.module_path = "/home/oliv/.puppet/modules"
  #end

  config.vm.forward_port 80, 4567   
  config.vm.forward_port 9010, 4568

  #to run the tests in your host env
  config.vm.forward_port 27017, 27017
  config.vm.forward_port 6379, 6379

  #to allow pushing messages to the default transports
  config.vm.forward_port 2221, 2221
  config.vm.forward_port 2222, 2223

  config.vm.network :hostonly, "10.11.12.13"
  config.vm.share_folder('frontend', '/var/vusion/app', 'app', :nfs => true)
  config.vm.share_folder('vusion', '/var/vusion/backend/vusion', 'backend/vusion', :nfs => true)
  config.vm.share_folder('transports', '/var/vusion/backend/transports', 'backend/transports', :nfs => true)
  config.vm.share_folder('middlewares', '/var/vusion/backend/middlewares', 'backend/middlewares', :nfs => true)
  config.vm.share_folder('components', '/var/vusion/backend/components', 'backend/components', :nfs => true)
  config.vm.share_folder('tests', '/var/vusion/backend/tests', 'backend/tests', :nfs => true)
  config.vm.share_folder('dispatchers', '/var/vusion/backend/disptachers', 'backend/dispatchers', :nfs => true)
  config.vm.share_folder('scripts', '/var/vusion/backend/scripts', 'backend/scripts', :nfs => true)

end
