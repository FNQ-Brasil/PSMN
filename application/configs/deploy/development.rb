server 'localhost', :web, :app, :db, primary: true

set :rails_env, 'development'
set :branch, 'master'
set :rvm_type, :system

#ssh_options[:forward_agent] = true
set :user, 'SSI'
#set :port, 2222
set :deploy_to, "/home/#{user}/apps/#{application}"

namespace :deploy do
  desc 'restart nginx and fpm'
  task :restart, roles: :app do
    run 'sudo /opt/nginx/sbin/nginx -s reload'
    run 'sudo /etc/init.d/php-fpm restart'
  end
end
