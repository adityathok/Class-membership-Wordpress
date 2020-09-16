# Class-membership-Wordpress
Class membership Wordpress

## Setup
Untuk menampilkan form
```
Member::formMember($args,$action,$arraymeta);
```
ada tiga jenis $action = 'add','edit','editpass'

untuk edit tambahkan 
```
$args[ID] = id user
```
untuk role, secara default adalah 'subscriber', dapat diset dengan 
```
$args[role] = 'role';
```
edit tidak akan menampilkan email, username dan password, gunakan 'editpass' untuk edit password 
