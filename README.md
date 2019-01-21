# DumpInMail
This is a small PHP application to let you send MySQL backup through email. Fork from backup2mail.
PPHPMailer dependencies are includes.

# Installation (You need a command line access)
  - Open “index.php” in you text-editor and change settings. If you’re not sure about something, leave the default values. Database settings, full path to script and your e-mail are required.
  - Upload folder in your public web folder to test it. Open the file in your browser. If you see the black page with green letters and there’s no errors, you can proceed.
  - Open “.htaccess” file provided in the package and remove “#” from the first line to enable script protection. If you lost this file, simply create new .htaccess file, add “deny from all” (without quotes) and save it.
  - Connect with SSH to your server, type crontab -e (to edit Cron schedule table) and the text editor should show up.
  - Add the following line:

    ```0 0 * * * php /path/to/DumpInMail/index.php >/dev/null 2>&1```

    (Numbers and asterisks are the interval part, see the cheat sheet below.
    php /home/your_account/DumpInMail/index.php means that PHP will execute the script, and >/dev/null 2>&1 tells Cron not to send output to e-mail specified in the first line of Cron configuration file.)

    Replace “your_account” with your account username, and adjust the interval (the above is everyday at midnight).
    ```Interval cheat sheet
    * * * * *	every minute
    0 0 * * *	every day at midnight
    0 5 * * *	every day at five o'clock in the morning
    (11 = 11AM, 23 = 11PM)
    0 0 * * 0	every Sunday at midnight
    (0 = Sunday, 1 = Monday, ...)```
    
  - Hit Control + X to close the file, type Y to save changes, press Enter to confirm.
  - Type crontab -l to check if everything is set properly. If it is, exit command line.
  - Wait for the first backup to arrive in your mailbox.
  - If you received the backup file, you're done! Enjoy!
  
 # Common Issues
  If you only see "Setup" part and any other, your php user (usually www-data) may not have permissions to edit log file.
