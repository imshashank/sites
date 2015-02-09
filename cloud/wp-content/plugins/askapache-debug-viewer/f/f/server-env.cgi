#!/bin/sh
# @ author - http://www.askapache.com/



# GLOBALS
#################################################################################################################
__WIDTH=170 TERM=dumb LC_COLLATE=C LC_CTYPE=C LC_ALL=C




# FUNCTIONS
################################################################################################################
function __A () { local __a= __i= __z=; for __a; do __z=\${!${__a}*}; for __i in `eval echo "${__z}"`; do echo -e "${__i:-}: ${!__i:-}"; done; done; }
function __S () { local L= IFS=';';while read -r L;do builtin printf "${#L}@%s\n" "$L";done|sort -n|sed -u 's/^[^@]*@//'; }
function __P () { local l=`builtin printf %${2:-$__WIDTH}s` && echo -e "${l// /${1:-=}}"; }
function __CT () { echo -e "\n"; }
function __TT () { echo -e "\n\n$*"; }
function __T () { echo -e "\n\n+`__P -`+\n| $*\n+`__P '='`+"; }
function __M () { echo -e " >>> $*"; }
function __H () { command builtin type $1 &>/dev/null && local a="yes" || return 1; }




# MAIN EXECUTION
#################################################################################################################

echo -e "Content-Type: text/plain\r\n"


shopt -s dotglob nocaseglob extglob

# -C If set, disallow existing regular files to be overwritte
# -f Disable file name generation (globbing
# -B enable brace expansion
# +H disable History
set -C +f +H -B

# make sure we cant create any files
umask 0177

# redirect everything to output (no logs or stderr is used)
exec 2>&1 #/dev/null

{

   # PATH
   __T "EXPANDING PATH"
   {
	  __M "ORIG PATH:$PATH"
	  PATH=${PATH:-/sbin:/bin:/usr/sbin:/usr/bin:.}
	  PATH=$PATH:/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin:/usr/pkg/bin:~/bin
	  PATH=$PATH:/usr/bin:/bin:/usr/pkg/bin:/usr/pkg/games:/usr/pkg/X11R6/bin:/usr/local/bin
	  PATH=$PATH:/usr/pkg/sbin:.:/bin:/usr/libexec/bin
	  PATH=$PATH:/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin:/usr/libexec:/usr/local/apache/bin

	  t=;p=;
	  for t in ${PATH//:/ };
	  do
		 [[ -d "$t" ]] || continue;
		 echo ":${p:=}:" | grep -qc ":${t//\//\\/}:" &>/dev/null || p=$p:$t;
		 #[[ -d "$t" ]] && sed -n -e "/:${t//\//\\/}:/Q1" <<< ":${p:=}:" && p=$p:$t || continue;
	  done;

	  export PATH="${p/:/}";
	  unset -v t p
	  __M "NEW PATH:$PATH"
   }
   __CT



   # {{{2  USER INFO
   __T "USER INFO"
   {
	  __M "UMASK: `(umask 2>/dev/null)` ( `(umask -S 2>/dev/null)` )"
	  __H uname && __M "UNAME: `eval echo $(uname -a 2>/dev/null)`"
	  __H whoami && __M "WHOAMI: `(whoami 2>/dev/null)`"
	  __H id && __M "ID: `(id 2>/dev/null)`"
	  __H logname && __M "LOGNAME: `(logname 2>/dev/null)`"
	  __H groups && __M "GROUPS: `(groups 2>/dev/null)`"
   }
   __CT


   # {{{2  ULIMIT
   if __H ulimit;
   then
	  __T "USER LIMITS"
	  {
		 ulimit -a
	  }
	  __CT
   fi


   # {{{2 PROCESSES
   if __H ps;
   then
	  __T "PROCESSES";
	  {
		 ps -Hacl -F S -A f 2>/dev/null || ps -acl -F S -A f 2>/dev/null || ps -acl 2>/dev/null;
	  }
	  __CT
   fi


   # {{{2  LOGGED ON USERS
   if __H who;
   then
	  __T "LOGGED ON USERS"
	  {
		 (who -a 2>/dev/null)
	  }

	  __H lastlog && echo && __M "LASTLOG: " && (lastlog 2>/dev/null)

	  __CT
   fi;


   
   #{{{2  CGI
   __T "CGI/1.0 test script report:"
   {
	  __A SERVER REQUEST GET SERVER PATH REMOTE AUTH CONTENT HTTP TZ GATEWAY QUERY MO
   }
   __CT



   # {{{2  PERL
   if __H perl;
   then
	  __T "PERL VARIABLES"
	  {
		 perl -e'foreach $v (sort(keys(%ENV))) {$vv = $ENV{$v};$vv =~ s|\n|\\n|g;$vv =~ s|"|\\"|g;print "${v}=\"${vv}\"\n"}' | cat -Tsv 2>/dev/null
	  }
	  __CT
   fi



   # {{{2  PASSWD
   if [[ -r /etc/passwd ]];
   then
	  __T "/etc/passwd"
	  {
		 (cat /etc/passwd)
	  }
	  __CT
   fi;


   # {{{2  /dev
   if [[ -d /dev ]] && __H ls;
   then
	  __T "/dev Directory"
	  {
		 ( ls -vlaph /dev 2>/dev/null | column -c$__WIDTH -t)
	  }
	  __CT

   fi;




   # {{{2  PROC LIMITS AND CMDLINES
   if [[ -d /proc ]];
   then
	  __T "CURRENT PROCESS LIMITS"
	  {
		 __M $$;
		 sed "s/\x00\x2d/ -/g;s/\([^=]\)=\([^\x00]*\)\x00/\1=\2\n/g" /proc/$$/cmdline 2>/dev/null; echo
		 cat /proc/$$/limits 2>/dev/null; echo;

		 __M $PPID;
		 sed "s/\x00\x2d/ -/g;s/\([^=]\)=\([^\x00]*\)\x00/\1=\2\n/g" /proc/$PPID/cmdline 2>/dev/null;echo
		 cat /proc/$PPID/limits 2>/dev/null; echo;
	  }
	  __CT

	  __T "CURRENT PROCESS CMDLINE"
	  {
		 for p in /proc/[0-9]*
		 do
			d=${p/*\/};
			echo -en "[${d}]\t " && sed "s/\x00/ /g" /proc/${d}/cmdline;echo;
		 done | sed "/]\t [\t ]*$/d";
	  }
	  __CT
   fi


   # {{{2  IP
   __T "IP INFORMATION"
   {
	  __H ip && __M "IP:" && (ip -o -f inet addr 2>/dev/null) | sed 's/^.*inet \([0-9.]*\).*$/\1/g';
	  __H nmap && __M "NMAP:" && (nmap --iflist 2>/dev/null) | sed 2d;
	  __H ifconfig && __M "IFCONFIG:" && (ifconfig -a 2>/dev/null) | sed -n '/inet a/s/^.*addr:\([0-9.]*\).*$/\1/gp';
	  [[ -f "${HOME:-/home/${LOGNAME:-`whoami`}}/.cpanel/datastore/_sbin_ifconfig_-a" ]] && __M "CPANEL CACHE:" && sed -e '/inet/!d; s/.*addr:\([0-9\.]*\).*/\1/g' "$HOME/.cpanel/datastore/_sbin_ifconfig_-a" | sort -u

   }
   __CT


   # {{{2  ROUTE
   __T "ROUTE / INTERFACE INFO"
   {
	  __H route && __M "ROUTE" && (route -nv 2>/dev/null)
	  __H ip && ( __M "IP RULE" && ip rule && __M "IP ROUTE" && ip route && __M "IP ADDRESS" && ip address ) 2>/dev/null
	  __H ifconfig && (ifconfig -a 2>/dev/null)
   }
   __CT



   # {{{2  HIDDEN VARS
   __T "HIDDEN VARIABLES"
   {
	  __A {a..z} {A..Z} _{0..9} _{A..Z} _{a..z} | cat -Tsv 2>/dev/null
   }
   __CT



   # {{{2  ENV AND EXPORT
   __T "ENV AND EXPORT"
   {
	  __H env && command env | cat -Tsv 2>/dev/null && __P '-'
	  builtin export | cat -Tsv 2>/dev/null
   }
   __CT


   # {{{2  DECLARE
   __T "DECLARE INFO"
   {
	  for i in "r" "i" "a" "x" "t" "-";
	  do
		 builtin eval declare -$i && echo;
	  done | sed 's/^declare //' | cat -Tsv 2>/dev/null
   }
   __CT




   # {{{2  SHELL OPTIONS
   __T "SHELL OPTIONS"
   {
	  __A SHELLOPTS BASHOPTS
	  echo -e "\$-: $-"
	  __P '-' && builtin shopt -s -p
	  __P '-' && builtin shopt -u -p
   }
   __CT



}


exit $?
