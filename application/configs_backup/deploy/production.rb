server 'fnq-web-2e.dualtec.com.br', :web, :app, :db, primary: true

set :rails_env, 'production'
set :branch, 'master'
set :rvm_type, :user

ssh_options[:forward_agent] = true
set :user, 'psmn'
set :port, 2222

namespace :deploy do
  desc 'restart nginx and fpm'
  task :restart, roles: :app do
    run 'sudo service nginx restart'
    run 'sudo service php-fpm restart'
  end

end