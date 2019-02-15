del .\export\channelengine-prestashop.zip
xcopy /r /d /i /y /s /exclude:.xcopyignore . %TEMP%\channelengine-magento2
7z a -r .\export\channelengine-magento2.zip -w %TEMP%\channelengine-magento2\*
rd /s /q %TEMP%\channelengine-magento2\