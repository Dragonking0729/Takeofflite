# Takeoff Lite Estimating Tool Project

Demo URL for user id 710
https://estimating.takeofflite.com/token/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo3MTB9.qH90bvPI1-5ox7Jm6uLq-Q_5LvoGErxUjcCrlO1bHts

Preparation
Estimating tool is using spatie/pdf-to-image (https://github.com/spatie/pdf-to-image) package to convert PDF to image so you will need to install GhostScript and add PHP imagick extension before you do composer install.

Environment: 
- Windows 10, 11
- PHP 7.3.27

Step1: Install GhostScript
- You can download source from this link(https://drive.google.com/file/d/1NN2eLZIGdSLtdtjCyOUc8Hodb-m5vUyI/view?usp=sharing)
- Install ghostscript according to OS version (32/64bit)
- Update GS_PATH env value file. i.e: GS_PATH='C:\Program Files\gs\gs9.52\bin\gswin64c.exe'

Step2: Install Imagick PHP extension
- Donwload source from this link:
PHP 7.2 (https://drive.google.com/file/d/1SddDbqbhB0OuK8xcpoz255aPgLD2HuCu/view?usp=sharing)
PHP 7.3(https://drive.google.com/file/d/1FNN7YzgGH5SDfHKShgRHt25Uepa-rM5O/view?usp=sharing)
- Follow this link: https://ourcodeworld.com/articles/read/349/how-to-install-and-enable-the-imagick-extension-in-xampp-for-windows

Reference Link: 
- How To Install Imagick Extension in XAMPP(https://www.youtube.com/watch?v=qZ9_rq6c9uY&ab_channel=MuruganS)
- Convert Pdf to Images using Spatie - Laravel (https://www.youtube.com/watch?v=8PUcLVgU2u0&list=LL&index=3&ab_channel=Engineer%27sCommunityGuide)


Thank you!
