rt11 macro scromd.mac
rt11 link scromd
del scromd.obj
copy scromd.sav .\release\scromd.sav
rt11 copy/predelete scromd.sav ld0:scromd.sav
del scromd.sav
