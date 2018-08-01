#!/usr/bin/env python
import urllib.request,sys

if(len(sys.argv)<5):
    print("key2url URL COUNT PASSWORD KEYFILE")
    sys.exit()


url = sys.argv[1]
count = sys.argv[2]
passwd = sys.argv[3]
keyfile = sys.argv[4]
f = open(keyfile,'rb')
urlfile = open('URL_%s'%keyfile,'wb')

while True:
    key = f.readline()
    if not key:break;
    fullurl = "%s?q=%s&count=%s&key=%s&format=html"%(url,urllib.request.quote(key),count,passwd)    
    print(fullurl)

f.close()
urlfile.close()
