#!/bin/bash
# (C) 2014 Gregory Karpinsky
# https://gist.github.com/tivnet/8256140

# Path to the PHP executable
php="%PHP%"

for ((n=1; n <= $#; n++)); do
    if [ -e "${!n}" ]; then
        # Converts Unix style paths to Windows equivalents
        path="$(cygpath --mixed ${!n} | xargs)"

        case 1 in
            $(( n == 1 )) )
                set -- "$path" "${@:$(($n+1))}";;
            $(( n < $# )) )
                set -- "${@:1:$((n-1))}" "$path" ${@:$((n+1)):$#};;
            *)
                set -- "${@:1:$(($#-1))}" "$path";;
        esac
    fi
done

"$php" "$@"
