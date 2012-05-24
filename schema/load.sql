update globals set curr_contest_id = 1;

insert into tags set tag = 'default';

insert into contests (contest_type, contest_name, time_start, time_length, tag, metadata, status) values ('speed', 'Test', 0, 3600, 'default', '{"time_freeze":"0"}', 1);

insert into divisions (division_name) values ('adv'), ('nov');

insert into contests_divisions (contest_id, division_id, metadata) values (1, 1, '{}'), (1, 2, '{}');
