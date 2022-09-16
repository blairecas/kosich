@echo off
echo.
echo ===========================================================================
echo Cleanup
echo ===========================================================================
del _kosich.lst
del kosich.sav

echo.
echo ===========================================================================
echo Compiling KOSICH.MAC
echo ===========================================================================
rt11 macro kosich.mac/list:kosich.lst
rem if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Linking KOSICH.OBJ
echo ===========================================================================
rt11 link kosich
rem if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Cleanup, Writing to .DSK
echo ===========================================================================
del kosich.obj
rt11 copy/predelete kosich.sav ld0:kosich.sav
move /y KOSICH.LST _kosich.lst >nul
move /y KOSICH.SAV release\kosich.sav >nul
echo.
