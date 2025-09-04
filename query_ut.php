<?php

if (isset($filter_totem)){
  $filter_totem_ok= " utilizza_totem=true and ";
} else{
  $filter_totem_ok= " "; 
}

$query_ut="select  cmu.id_uo, u.id_ut, u.descrizione
  from topo.ut u 
  join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
  where ".$filter_totem_ok." id_ut in   
  (select 
    id_ut
    from util.sys_users_ut suu where id_user in (
        select id_user from util.sys_users su where \"name\" ILIKE $1
  )   
  and id_ut > 0
  and coalesce(u.data_disattivazione, (now()+ interval '1' year)) > now()
  union 
  select 
  u.id_ut 
    from util.sys_users_ut suu, topo.ut u
    where suu.id_user in (
        select id_user from util.sys_users su where \"name\" ILIKE $1
  )   
  and suu.id_ut = -1
  ) and id_ut in (select id_uo_sit from anagrafe_percorsi.cons_mapping_uo)
  and coalesce(u.data_disattivazione, (now()+ interval '1' year)) > now()
  order by 3";

?>