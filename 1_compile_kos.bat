@echo off
echo.
echo ===========================================================================
echo Cleanup
echo ===========================================================================
del _kosich.lst

echo.
echo ===========================================================================
echo Compiling
echo ===========================================================================
rt11 macro kosich.mac/list:kosich.lst

echo.
echo ===========================================================================
echo Linking KOSICH.OBJ
echo ===========================================================================
rt11 link kosich

echo.
echo ===========================================================================
echo Cleanup, Writing to .DSK
echo ===========================================================================
del kosich.obj
rt11 copy/predelete kosich.sav ld0:kosich.sav
move /y KOSICH.LST _kosich.lst >nul
move /y KOSICH.SAV release\kosich.sav >nul

echo.
