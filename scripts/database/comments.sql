<<<<<<< HEAD
create table comments
(
  comment_id int auto_increment
    primary key,
  article_id int not null,
  comment_text text not null,
  author_id int not null,
  date_created timestamp default CURRENT_TIMESTAMP not null,
  active tinyint(1) default '0' null,
  parent_id int null,
  constraint comments_comments_comment_id_fk
  foreign key (parent_id) references joshutt_staging.comments (comment_id)
)
;

create index comments_article_id_date_created_index
  on comments (article_id, date_created)
;

create index comments_comment_id_index
  on comments (comment_id)
;

create index comments_parent_id_date_created_index
  on comments (parent_id, date_created)
;
=======
create table comments
(
  comment_id int auto_increment
    primary key,
  article_id int not null,
  comment_text text not null,
  author_id int not null,
  date_created timestamp default CURRENT_TIMESTAMP not null,
  active tinyint(1) default '0' null,
  parent_id int null,
  constraint comments_comments_comment_id_fk
  foreign key (parent_id) references joshutt_staging.comments (comment_id)
)
;

create index comments_article_id_date_created_index
  on comments (article_id, date_created)
;

create index comments_comment_id_index
  on comments (comment_id)
;

create index comments_parent_id_date_created_index
  on comments (parent_id, date_created)
;
>>>>>>> 5e27482dbbbc4c7969cfce51101d875bf010a0c6
