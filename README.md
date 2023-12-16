# MQTT2MYSQL Class

PHP class to subscribe to a topic in an MQTT broker and save it in a Mysql database.

A classe mqtt2mysql utiliza a classe [php-mqtt/client](https://packagist.org/packages/php-mqtt/client) de autoria de [Marvin Mall](https://github.com/namoshek). 

## Contributing
Please read [CONTRIBUTING.md](https://github.com/albandes/helpdezk/blob/master/CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Instalation
The package is available on packagist.org and can be installed using composer:

```bash
composer require php-mqtt/client
```

The package requires PHP version 7.4 or higher.

## Versioning
We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags).

## Authors
* **Rogério Albandes** - *Initial work* - [albandes](https://github.com/albandes)
* **Valentin Acosta**  - *Initial work* - [valentin](https://github.com/vilaxr)

See also the list of [contributors](https://github.com/albandes/helpdezk/contributors) who participated in this project.


## Create the service in systemd.

* **Configuration**

    On AWS Linux, the directory where the configuration files are located is /lib/systemd/system, check your distribution.
    Create the mqtt2mysql.service file

    ```
    [Unit]
    Description=Php Subscribe Mqtt and write in Mysql
    After=php-fpm.service

    [Service]
    Type=idle
    User=<Yor user>
    WorkingDirectory=<Your working directory>
    ExecStart=<php location> <mqtt-listen.php>
    Restart=on-failure

    [Install]
    WantedBy=multi-user.target
    ```

* **Commands**

    ```bash
    systemctl start mqtt2mysql.service
    ```
    ```bash
    systemctl stop mqtt2mysql.service
    ```
    ``` bash
    systemctl status mqtt2mysql.service
    ```
    ``` bash
    systemctl enable mqtt2mysql.service
    ```
    
* **References**    
    https://www.shubhamdipt.com/blog/how-to-create-a-systemd-service-in-linux/    
    https://medium.com/@benmorel/creating-a-linux-service-with-systemd-611b5c8b91d6
  


## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details



