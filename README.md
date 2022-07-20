# KIB3 StuPro SS 22

This Moodle plugins allows integrates Moodle and a Jupyter Notebooks to offer a virtual programming environment.

This plugin connects to a jupyterhub server and authenticates the Moodle users on the jupyterhub-server. That way they
can access a jupyter-notebook from within Moodle. Further development includes the options to submit solved
notebooks and to distribute notebooks by the teacher top the students.



# How to use this repository #

There's an .editorconfig in this repo, please use it while working on it
[VS Code Extension](vscode://extension/EditorConfig.EditorConfig)

## Environment Setup
There are two directories including some docker-based setups to start a [Moodle](./moodle_docker/README.md) and a 
 [JupyterHub-Instance](./jupyterhub_docker/README.md). If you got both of them running, you can install the Moodle-Plugin 
as described in the dedicated [Readme.md](./jupyter/README.md)

## Authentication ##

navigate to the according [Readme](./jupyter/auth/README.md) for instruction how to authenticate moodle with jupyterhub

## Access UI Prototype ##

0. perform a composer update `composer update` to update dependencies (you need to have composer installed: https://getcomposer.org/download/)
1. make sure you have a running moodle environment and installed the jupyter plugin as described above
2. open [http://127.0.0.1/mod/jupyter/ui/manage.php](http://127.0.0.1/mod/jupyter/ui/manage.php) in your browser

## License ##

2022 KIB3 Student Project

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

## Additional Resources
* [Moodle official development main page](https://docs.moodle.org/dev/Main_Page)
* [Moodle official output api page](https://docs.moodle.org/dev/Output_API)
* [Moodle official javascript page](https://docs.moodle.org/dev/Javascript_Modules)
* [Moodle official development block page](https://docs.moodle.org/dev/Blocks)
* [Moodle programming course](https://www.youtube.com/playlist?list=PLgfLVzXXIo5q10qVXDVyD-JZVyZL9pCq0)
