#!/bin/bash

# Fix user rights
sudo usermod -a -G apache ec2-user
sudo chown -R ec2-user:apache /var/www/api-gw
sudo chmod 2775 /var/www/api-gw
find /var/www/api-gw -type d -exec sudo chmod 2775 {} \;
find /var/www/api-gw -type f -exec sudo chmod 0664 {} \;
