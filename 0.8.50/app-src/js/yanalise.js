/*********************************************
 * app-src/js/yanalise.js
 * YeAPF 0.8.50-1 built on 2016-08-22 17:09 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-08-22 17:09:30 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * yLexObj introduced in 2016-08-22 0.8.50-0
**********************************************/
"use strict";

// ==== YeAPF - Javascript implementation
function yAnalise(aLine, aStack)
{
  if (aLine!==undefined) {

    aLine = unmaskHTML(aLine);

    var yPattern = /%[+(\w)]|[]\(/gi;
    var yFunctions = ',int,integer,intz,intn,decimal,ibdate,tsdate,tstime,date,time,words,image,nl2br,quoted,singleQuoted,condLabel';
    var p,p1,p2,c1,c2,p3;
    var aValue='';

    while ((typeof aLine=='string') && (p = aLine.search(yPattern)) >=0 ) {
      p1 = aLine.slice(p).search(/\(/);
      c1 = aLine.slice(p + p1 + 1, p + p1 + 2);
      if ((c1=='"') || (c1=="'")) {
        p3 = p + p1 + 1 ;
        do {
          p3++;
          c2 = aLine.slice(p3, p3 + 1);
        } while ((c2!=c1) && (p3<aLine.length));
        p2 = p3 + aLine.slice(p3).search(/\)/) - p;
      } else
        p2 = aLine.slice(p).search(/\)/);

      var funcName = aLine.slice(p+1, p+p1);
      var funcParams = aLine.slice(p + p1 + 1, p + p2);
      var parametros = funcParams;
      funcParams = funcParams.split(',');
      for (var n=0; n<funcParams.length; n++)
        funcParams[n] = yAnalise(funcParams[n], aStack);

      aValue = undefined;
      var fParamU = funcParams[0].toUpperCase();
      var fParamN = funcParams[0];
      if (aStack!==undefined) {
        // can come a stack or a simple unidimensional array
        if (aStack[0]==undefined) {
          if (aStack[fParamU])
            aValue = yAnalise(aStack[fParamU], aStack);
          else
            aValue = yAnalise(aStack[fParamN], aStack);
        } else {
          for(var sNdx=aStack.length -1 ; (sNdx>=0) && (aValue==undefined); sNdx--)
            if (aStack[sNdx][fParamU] != undefined)
              aValue = yAnalise(aStack[sNdx][fParamU], aStack);
            else if (aStack[sNdx][fParamN] != undefined)
              aValue = yAnalise(aStack[sNdx][fParamN], aStack);
        }
      } else {
        if (eval('typeof '+fParamN)=='string')
          aValue=eval(fParamN);
        else
          aValue=yAnalise(fParamN);
      }

      if (aValue==undefined)
          aValue = '';
      funcParams[0] = aValue;

      switch (funcName)
      {
        case 'integer':
        case 'int':
        case 'intz':
        case 'intn':
          aValue = str2int(aValue);
          if (aValue==0) {
            if (funcName=='intz')
              aValue='-';
            else if (funcName=='intn')
              aValue='';
          }
          break;
        case 'decimal':
          var aDecimals = Math.max(0,parseInt(funcParams[1]));
          aValue = parseFloat(aValue);
          aValue = aValue.toFixed(aDecimals);
          break;
        case 'ibdate':
          aValue = IBDate2Date(aValue);
          break;
        case 'tsdate':
          aValue = timestamp2date(aValue);
          break;
        case 'tstime':
          aValue = timestamp2time(aValue);
          break;
        case 'date':
          if (funcParams[1])
            aValue = UDate2Date(aValue, funcParams[1]);
          else
            aValue = UDate2Date(aValue);
          break;
        case 'time':
          if (funcParams[1])
            aValue = UDate2Time(aValue, funcParams[1]);
          else
            aValue = UDate2Time(aValue);
          break;
        case 'nl2br':
          aValue = aValue.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
          break;
        case 'words':
          var auxValue = aValue.split(' ');

          var aStart = Math.max(0,str2int(funcParams[1]));
          var aCount = Math.max(auxValue.length - 1 ,str2int(funcParams[2]));
          var aWrap  = Math.max(0,str2int(funcParams[3]));

          aValue='';
          for (var n=aStart; n<aStart+aCount; n++) {
            var tmpValue = onlyDefinedValue(auxValue[n]);
            if (tmpValue>'')
              aValue+=' '+tmpValue;
          }

          if (aWrap>0)
            aValue = wordwrap(aValue, aWrap, '<br>', true);

          break;
        case 'quoted':
          aValue = ('"'+aValue).trim()+'"';
          break;
        case 'singleQuoted':
          aValue = ("'"+aValue).trim()+"'";
          break;
        case 'condLabel':

          break;
        default:
          if (funcName>'') {
            if (eval('typeof '+funcName) == 'function') {
              var parametros='';
              for (var n=0; n<funcParams.length; n++) {
                if (parametros>'')
                  parametros += ','

                parametros += "'"+funcParams[n]+"'";
              }

              var chamada = '<script>'+funcName+'('+parametros+');</'+'script>';
              aValue = chamada.evalScripts();
            }
          }
          break;
      }

      aLine = aLine.slice(0,p) + aValue + aLine.slice(p + p2 + 1);

    }
    var needEval=false;
    var ops=['<', '>', '==', '!=', '<=', '>='];
    for(var i=0; i<ops.length; i++) {
      needEval|=(aLine.indexOf(ops[i])>=0);
    }
    /* disabled until we can recognize quickly if is an HTML code */
    if ((needEval) && (true)) {
      try{
        aLine=eval(aLine);
      } catch(err) {

      }
    }

  } else
    aLine='';

  return aLine;
}

/* YeAPF lexical analyser and parser  - Javascript implementation */
var yLexObj = function(aString) {
  "use strict";
  var self = {};

  self.optable = {
    '!': 'EXCLAMATION',
    '"': 'DOUBLE_QUOTE',
    '#': 'NUMBER_SIGN',
    '$': 'DOLLAR',
    '%': 'MODULUS',
    '^': 'POWER',
    '&': 'AMPERSAND',
    '(': 'L_PAREN',
    ')': 'R_PAREN',
    '*': 'MULTIPLICATION',
    '+': 'ADDITION',
    ',': 'COMMA',
    '-': 'SUBSTRACTION',
    '.': 'PERIOD',
    '/': 'DIVISION',
    ':': 'COLON',
    ';': 'SEMICOLON',
    '<': 'LESS_THAN',
    '=': 'EQUALS',
    '>': 'GREATER_THAN',
    '?': 'QUESTION',
    '[': 'L_BRACKET',
    '\\': 'BACKSLASH',
    ']': 'R_BRACKET',
    '{': 'L_BRACE',
    '|': 'PIPE',
    '}': 'R_BRACE',
    '~': 'TILDE',
    '++': 'INCREMENT',
    '--': 'DECREMENT',
    '==': 'EQUAL2',
    '!=': 'NOT_EQUAL2',
    '>=': 'GREATER_EQUALS2',
    '<=': 'LESS_EQUALS2'
  };

  self.opprecedence = {
    'LIKE':  5,
    'AND':  5,
    'OR':   5,
    '<':  5,
    '>':  5,
    '<=': 5,
    '>=': 5,
    '==': 5,
    '(':  4,
    '^':  3,
    '/':  2,
    '*':  2,
    '+':  1,
    '-':  1
  };

  self._ALPHA = 1;
  self._ALPHA_NUM = 2;
  self._NEW_LINE = 4;
  self._DIGIT = 8;
  self._QUOTE = 16;

  self.void = {
      type: null,
      token: null,
      token_string: null,
      pos: null
  };

  self.error = function() {
    var ret=JSON.parse(JSON.stringify(self.void));
    ret.type = 'ERROR';
    ret.pos = self.pos;
    return ret;
  };


  self.char = function(offset) {
    offset = offset || 0;
    return self.buf.charAt(self.pos + offset);
  };

  self._isnewline = function(c) {
    c = self.char();
    return (c === '\r' || c === '\n') ? self._NEW_LINE : 0;
  };

  self._isdigit = function(c) {
    return (c >= '0' && c <= '9') ? self._DIGIT : 0;
  };

  self._isalpha = function(c) {
    return ((c >= 'a' && c <= 'z') ||
      (c >= 'A' && c <= 'Z') ||
      (c === '_') || (c === '$')) ? self._ALPHA : 0;
  };

  self._isalphanum = function(c) {
    return self._isdigit(c) | self._isalpha(c);
  };

  self._isquote = function(c) {
    return ((c == "'") || (c == '"')) ? self._QUOTE : 0;
  };

  self._whatis = function(c) {
    return self._isalpha(c) | self._isdigit(c) | self._isquote(c);
  };

  self._process_quote = function() {
    var quote = self.char(),
      lq_pos = self.buf.indexOf(quote, self.pos + 1),
      ret = self.error();
    if (lq_pos > self.pos) {
      ret = {
        type: 'LITERAL',
        token: self.buf.substring(self.pos + 1, lq_pos),
        pos: self.pos
      };
      ret.token_string=ret.token;
      self.pos = lq_pos + 1;
    }
    return ret;
  };

  self._process_identifier = function() {
    var lq_pos = 1,
      ret = self.error();
    while ((self.pos + lq_pos < self.buf.length) &&
      (self._isalpha(self.char(lq_pos))))
      lq_pos++;
    ret = {
      type: 'IDENTIFIER',
      token: self.buf.substring(self.pos, self.pos + lq_pos),
      pos: self.pos
    };
    ret.token_string=ret.token;
    self.pos = self.pos + lq_pos;
    return ret;
  };

  self._process_number = function() {
    var lq_pos = 1,
      ret = self.error();
    while ((self.pos + lq_pos < self.buf.length) &&
      (self._isdigit(self.char(lq_pos))))
      lq_pos++;
    ret = {
      type: 'NUMBER',
      token: self.buf.substring(self.pos, self.pos + lq_pos),
      pos: self.pos
    };
    ret.token_string=ret.token;
    self.pos = self.pos + lq_pos;
    return ret;
  };

  self.getToken = function() {
    var c,
      ret = self.error(),
      sep = ' \t\r\n';

    /* jump to next valid char */
    while (self.pos < self.buf.length) {
      c = self.char();
      if (sep.indexOf(c) > -1) {
        self.pos++;
      } else {
        break;
      }
    }

    /* if still into string */
    if (self.pos < self.buf.length) {
      var canProcessOp=true;
      if (c == '/') {
        if (self.char(1) == '*') {

        } else if (self.char(1) == '/') {

        }
      }

      if (canProcessOp) {
        var op = self.optable[c];
        if (op === undefined) {
          switch (self._whatis(c)) {
            case (self._ALPHA):
              ret = self._process_identifier();
              var auxToken=String(ret.token_string).toUpperCase()
              if ((auxToken=='AND') || (auxToken=='OR') || (auxToken=='LIKE')) {
                ret.token_string=auxToken;
                ret.type='OPERATOR';
              }
              break;
            case (self._DIGIT):
              ret = self._process_number();
              break;
            case (self._QUOTE):
              ret = self._process_quote();
              break;
          }
        } else {
          var _type='OPERATOR',
              _token_string = c,
              c1   = self.char(1),
              r1   = self.void,
              _pos = self.pos;
          if (self.optable[c + c1]) {
            c = c + c1;
            op = self.optable[c];
            _token_string = c;
          } else if ('-+'.indexOf(c)>=0)  {
            var ptt=self.priorToken.type;
            if ((ptt===null) || ((ptt=='OPERATOR') && (self.priorToken.token=="L_PAREN"))) {
              var c1t=self._whatis(c1);
              if (c1t==self._DIGIT) {
                r1 = self._process_number();
              } else if (c1t==self._ALPHA)
                r1 = self._process_identifier();
            }
          }
          
          ret = {
            type: r1.type || _type ,
            token: r1.token || op,
            pos: _pos,
            token_string: r1.token_string || _token_string
          };
          self.pos += c.length;
        }
      }
    } else {
      ret.type = 'EOF',
        ret.token = null;
    }
    self.priorToken=ret;
    return ret;
  };

  self.tokenTypeIs = function(token, expectedTypes) {
    expectedTypes=','+expectedTypes+',';
    return  (expectedTypes.indexOf(','+token.type+',')>=0);
  };

  self.getExpectedToken = function(expectedTypes) {
    var priorPos=self.pos;
    var token=self.getToken();
    if (self.tokenTypeIs(token, expectedTypes))  {
      return token;
    } else {
      self.pos=priorPos;
      return false;
    }
  };

  self._analiseText = function () {
    var token, lastSym=self.void, pct, ppt, itemSolved;
    do {
      itemSolved=false;
      token=self.getToken();
      if (token && token.type!="EOF") {
        if (self.symStack.length>0)
          lastSym=self.symStack[self.symStack.length-1];
        lastSym=lastSym || self.void;

        if (token.type=='OPERATOR') {
          pct=self.opprecedence[token.token_string] || 99;
          ppt=self.opprecedence[lastSym.token_string] || 10;
          if (pct<=ppt) {
            lastSym=self.symStack.pop();
            self.symStack.push(token);
            if (lastSym)
              if (lastSym.token_string!='(')
                self.postFixStack.push(lastSym);
            itemSolved=true;
          
          } else if (token.token_string==')') {
            do {
              lastSym=self.symStack.pop();
              if (lastSym) {
                if (lastSym.token_string!='(')
                  self.postFixStack.push(lastSym);
              }
            } while ((lastSym) && (lastSym.token_string!='('));
            itemSolved=true;
          }
        }
        
        if (!itemSolved) {
          if (token) {
            if (token.type=='OPERATOR') {
              self.symStack.push(token);
            } else {
              self.postFixStack.push(token);
            }
          }
        }
      }

    } while (!((token.type=='ERROR') || (token.type=='EOF')));

    do {
      lastSym=self.symStack.pop();
      if ((lastSym) && (lastSym.type!='EOF'))
        self.postFixStack.push(lastSym);
    } while ((lastSym) && (lastSym.type!='EOF'));

    if (false) {
      console.log("postFixStack:");
      self.showStack(self.postFixStack);
      console.log("symStack:");
      self.showStack(self.symStack);
    }
  };

  self.solve = function(data) {
    var i, stack=[], token, aux, canPush, op1, op2, ret;
    for (i=0; i<self.postFixStack.length; i++) {
      canPush=false;

      token=self.postFixStack[i];
      if (token) {
        if ((token.type=='NUMBER') || (token.type=='LITERAL')) {
          aux=String(token.token_string).toUpperCase();
          canPush=true;
        }
        if (token.type=='IDENTIFIER') {
          aux=String(data[token.token_string]).toUpperCase();
          if (typeof aux=='undefined') {
            var errorMessage="'"+token.token_string + "' is not defined on data";
            console.error(errorMessage);
            throw new Error(errorMessage)
          }
          else
            canPush=true;
        }

        if (canPush) {
          stack.push(aux);
        } else {
          op2=stack.pop();
          op1=stack.pop();
          ret=null;
          switch ((""+token.token_string).toUpperCase()) {
            case '+':
              ret = op1+op2;
              break;
            case '-':
              ret = op1-op2;
              break;
            case '*':
              ret = op1*op2;
              break;
            case '/':
              ret = op1/op2;
              break;
            case '^':
              ret = Math.pow(op1, op2);
              break;
            case '>':
              ret = op1>op2;
              break;
            case '<':
              ret = op1<op2;
              break;
            case '>=':
              ret = op1>=op2;
              break;
            case '<=':
              ret = op1<=op2;
              break;
            case '==':
              ret = op1==op2;
              break;
            case 'AND':
            case '&&':
              ret = op1 && op2;
              break;
            case 'OR':
            case '||':
              ret = op1 || op2;
              break;

            case 'LIKE':
              op1=String(op1);
              op2=String(op2);
              var cleanFilter=op2.replace(/\%/g,'');
              if (op2.substr(0,1)!='%') {
                if (op2.substr(op2.length-1)!='%') {
                  /* bla */
                  ret=op1==op2;
                } else {
                  /* bla% */
                  ret=op1.substr(0,cleanFilter.length)==cleanFilter;
                }
              } else {
                if (op2.substr(op2.length-1,1)=='%') {
                  /* %bla% */
                  ret=op1.indexOf(cleanFilter)>=0;
                } else {
                  /* %bla */
                  ret=op1.substr(op1.length-cleanFilter.length)==cleanFilter;
                }
              }
              break;
            default:
              var errorMessage="'"+token.token_string + "' is not a recognized operator";
              console.error(errorMessage);
              throw new Error();
              break;
          }

          if (false) console.log("{0} = {1} {2} {3}".format(ret, op1, token.token_string, op2));

          if (ret!==null)
            stack.push(ret);
        }
      }
    }
    ret=stack.pop();
    if (false) console.log(JSON.stringify(ret));
    return ret;
  };

  self.showStack = function (s) {
    var stackString="\t";
    for(var i=0; i<s.length; i++)
      stackString+=(s[i].token_string)+' ';
    console.log(stackString);
  };

  self.parse = function() {
    self.reset();
    self._analiseText();
    return self.stack;
  };

  self.reset = function () {
    self.pos = 0;
    self.symStack = [];
    self.postFixStack = [];
    self.priorToken=self.void;
  };

  self.init = function(aString) {
    self.buf = aString || self.buf || "";
    self.reset();
    return self;
  };

  return self.init(aString);
}
