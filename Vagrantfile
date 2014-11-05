Vagrant::configure("2") do |config|

  config.vm.hostname = "vusion"
  config.vm.box = "vusion"
  config.vm.box_url = "file:///Users/olivier/Development/vusion/vusion2.box"

  config.vm.network "forwarded_port", guest:80, host:4567   
  config.vm.network "forwarded_port", guest:9010, host:4568

  #to run the tests in your host env
  config.vm.network "forwarded_port", guest:27017, host:27017
  config.vm.network "forwarded_port", guest:6379, host:6379

  #to allow pushing messages to the default transports
  config.vm.network "forwarded_port", guest:2221, host:2221
  config.vm.network "forwarded_port", guest:2222, host:2223

  config.vm.network "private_network", ip:"10.11.12.13"
  config.vm.synced_folder "app", "/var/vusion/app",  type:"nfs"
  config.vm.synced_folder "lib", "/var/vusion/lib",  type:"nfs"
  config.vm.synced_folder "composer", "/var/vusion/composer",  type:"nfs"
  config.vm.synced_folder "backend/vusion", "/var/vusion/backend/vusion", type:"nfs"
  config.vm.synced_folder "backend/transports", "/var/vusion/backend/transports", type:"nfs"
  config.vm.synced_folder "backend/middlewares", "/var/vusion/backend/middlewares", type:"nfs"
  config.vm.synced_folder "backend/components", "/var/vusion/backend/components", type:"nfs"
  config.vm.synced_folder "backend/tests", "/var/vusion/backend/tests", type:"nfs"
  config.vm.synced_folder "backend/dispatchers", "/var/vusion/backend/disptachers", type:"nfs"
  config.vm.synced_folder "backend/scripts", "/var/vusion/backend/scripts", type:"nfs"

end
