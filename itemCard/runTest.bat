@echo off
echo Running printers application ...
start "" "printInvoiceMainPc.exe"
ping -n 2 127.0.0.1 > nul

echo Running xampp...
start "" "c:/xampp/xampp-control.exe"
ping -n 2 127.0.0.1 > nul

echo Opening the web page on localhost...
start "" "http://localhost/projectPoint-v1/point-v8/"

echo All tasks are complete.


echo Wellcom in programe Point 
echo *****************************
echo product by eng : Hazem Mostafa
echo *********************************
echo ******    *************    ******
echo *****      ************     *****
echo ********************************* 

pause