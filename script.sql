DROP DATABASE IF EXISTS social_network;
CREATE DATABASE social_network CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use social_network;

create table user(
id int not null primary key auto_increment,
firstname varchar(50) not null,
lastname varchar(50) not null,
email varchar(100) not null,
pass char(60) not null,
image varchar (250) default null,
admin int not null
)engine=InnoDB;

create unique index ix1 on user(email);


create table post(
id int not null primary key auto_increment,
content text,
user int not null,
date datetime not null default now(),
hidden int not null
)engine=InnoDB;

create table comment(
id int not null primary key auto_increment,
user int not null,
post int not null,
content text not null,
date datetime not null default now()
)engine=InnoDB;

create table likes(
id int not null primary key auto_increment,
user int not null,
post int not null
)engine=InnoDB;

create table tag_relations(
id int not null primary key auto_increment,
tag_id int not null ,
post_id int not null
)engine=InnoDB;

create table tags(
id int not null primary key auto_increment,
content varchar (250) not null
)engine=InnoDB;

create table report(
id int not null primary key auto_increment,
user_id int not null,
post_id int not null,
unique_report varchar (250) not null
)engine=InnoDB;


create unique index tags_content_uindex
	on tags (content);

	create unique index report_unique_report_uindex
	on tags (content);

alter table post add FOREIGN KEY (user) REFERENCES user(id);

alter table comment add FOREIGN KEY (user) REFERENCES user(id);
alter table comment add FOREIGN KEY (post) REFERENCES post(id);

alter table likes add FOREIGN KEY (user) REFERENCES user(id);
alter table likes add FOREIGN KEY (post) REFERENCES post(id);



