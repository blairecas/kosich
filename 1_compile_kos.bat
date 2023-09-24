@echo off
echo.
echo ===========================================================================
echo Cleanup
echo ===========================================================================
del _kosich.lst
del kosich.sav
del _kosic2.lst
del kosic2.sav

echo.
echo ===========================================================================
echo Compiling
echo ===========================================================================
rt11 macro kosich.mac/list:kosich.lst
rt11 macro kosic2.mac/list:kosic2.lst

echo.
echo ===========================================================================
echo Linking KOSICH.OBJ
echo ===========================================================================
rt11 link kosich
rt11 link kosic2

echo.
echo ===========================================================================
echo Cleanup, Writing to .DSK
echo ===========================================================================
del kosich.obj
del kosic2.obj
rt11 copy/predelete kosich.sav ld0:kosich.sav
move /y KOSICH.LST _kosich.lst >nul
move /y KOSICH.SAV release\kosich.sav >nul
rt11 copy/predelete kosic2.sav ld0:kosic2.sav
move /y KOSIC2.LST _kosic2.lst >nul
move /y KOSIC2.SAV release\kosic2.sav >nul

echo.
