@echo off
echo.
echo ===========================================================================
echo Compiling KOSPPU_PPU.MAC
echo ===========================================================================
php -f ..\scripts\preprocess.php kosppu_ppu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )
..\scripts\macro11.exe -ysl 32 -yus -l _kosppu_ppu.lst _kosppu_ppu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Creating PPU data block
echo ===========================================================================
php -f ..\scripts\lst2bin.php _kosppu_ppu.lst kosppu_cpu_ppu.mac mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Compiling KOSPPU_CPU.MAC
echo ===========================================================================
php -f ..\scripts\preprocess.php kosppu_cpu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )
..\scripts\macro11.exe -ysl 32 -yus -l _kosppu_cpu.lst _kosppu_cpu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Linking
echo ===========================================================================
php -f ..\scripts\lst2bin.php _kosppu_cpu.lst ./release/kosppu.sav sav
..\scripts\rt11dsk.exe d kosich.dsk .\release\kosppu.sav >NUL
..\scripts\rt11dsk.exe a kosich.dsk .\release\kosppu.sav >NUL

echo.