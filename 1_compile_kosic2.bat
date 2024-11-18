@echo off

echo.
echo ===========================================================================
echo Compiling KOSIC2_PPU.MAC
echo ===========================================================================
php -f ..\scripts\preprocess.php kosic2_ppu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )
..\scripts\macro11 -ysl 32 -yus -m ..\..\macro11\sysmac.sml -l _kosic2_ppu.lst _kosic2_ppu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Creating PPU data block
echo ===========================================================================
php -f ..\scripts\lst2bin.php _kosic2_ppu.lst kosic2_cpu_ppu.mac mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Compiling CPU.MAC
echo ===========================================================================
php -f ..\scripts\preprocess.php kosic2_cpu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )
..\scripts\macro11 -ysl 32 -yus -m ..\..\macro11\sysmac.sml -l _kosic2_cpu.lst _kosic2_cpu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Linking and cleanup
echo ===========================================================================
php -f ..\scripts\lst2bin.php _kosic2_cpu.lst ./release/kosic2.sav sav
if %ERRORLEVEL% NEQ 0 ( exit /b )

..\scripts\rt11dsk d kosich.dsk kosic2.sav >NUL
..\scripts\rt11dsk a kosich.dsk .\release\kosic2.sav >NUL

..\scripts\rt11dsk d ..\..\03_dsk\hdd.dsk kosic2.sav >NUL
..\scripts\rt11dsk a ..\..\03_dsk\hdd.dsk .\release\kosic2.sav >NUL

del _kosic2_cpu.mac
del _kosic2_cpu.lst
del _kosic2_ppu.mac
del _kosic2_ppu.lst

echo.