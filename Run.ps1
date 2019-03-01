php Course.php

Add-Type -AssemblyName presentationCore

$path = "F:\Success.mp3"

$player = New-Object System.Windows.Media.MediaPlayer

$player.Open($path)

Start-Sleep 1 #暂停一下，等待音乐文件加载完成

$time = $player.NaturalDuration.TimeSpan.TotalSeconds

$player.Play()

Start-Sleep $time

$player.Stop()

$player.Close()
