require('dotenv').config();
const WebSocket = require('ws');
const mysql = require('mysql2/promise');
const { v4: uuidv4 } = require('uuid');
const DB_HOST = process.env.DB_HOST || '127.0.0.1';
const DB_USER = process.env.DB_USER || 'root';
const DB_PASS = process.env.DB_PASS || '';
const DB_NAME = process.env.DB_NAME || 'enyooi';
const WS_PORT = parseInt(process.env.WS_PORT || '8082');
let pool;
async function initDB(){ pool = mysql.createPool({host:DB_HOST,user:DB_USER,password:DB_PASS,database:DB_NAME,waitForConnections:true,connectionLimit:10}); await pool.query('SELECT 1'); }
const wss = new WebSocket.Server({ port: WS_PORT });
const rooms = new Map();
function send(ws,obj){ try{ ws.send(JSON.stringify(obj)); }catch(e){} }
wss.on('connection', async (ws, req) => {
  try{
    const url = new URL(req.url, 'http://'+req.headers.host);
    const token = url.searchParams.get('token'); const room = url.searchParams.get('room');
    if(!token||!room){ ws.close(4001,'missing'); return; }
    const [rows] = await pool.query('SELECT idusuario FROM ws_tokens WHERE token=? AND expires_at>NOW() LIMIT 1',[token]);
    if(!rows.length){ ws.close(4003,'invalid_token'); return; }
    const uid = rows[0].idusuario;
    const [urows] = await pool.query('SELECT idusuario,usuario,nickname FROM usuarios WHERE idusuario=? LIMIT 1',[uid]);
    const user = urows[0] || {idusuario:uid,usuario:'U'+uid,nickname:''};
    ws.enyooi = {uid,user,room};
    if(!rooms.has(room)) rooms.set(room,new Set());
    rooms.get(room).add(ws);
    for(const client of rooms.get(room)){ if(client!==ws) send(client,{type:'user_joined',uid:user.idusuario,nickname:user.nickname||user.usuario}); }
    send(ws,{type:'connected',uid:user.idusuario,nickname:user.nickname||user.usuario});
    ws.on('message', async (msg)=>{
      let data; try{ data = JSON.parse(msg.toString()); }catch(e){return;}
      if(data.type==='chat'){
        const text = String(data.text||'').trim().slice(0,1000);
        if(!text) return;
        try{ await pool.query('INSERT INTO chat_messages (idstream,idusuario,message) VALUES (?,?,?)',[data.idstream||null, uid, text]); }catch(e){}
        for(const client of rooms.get(room)){ send(client,{type:'chat_message',uid:user.idusuario,nickname:user.nickname||user.usuario,text,ts:Date.now()}); }
      } else if(data.type==='tip'){
        const idcre = parseInt(data.idcreadora||0); const zaf = parseInt(data.zafiros||0);
        if(!idcre||!zaf||zaf<=0){ send(ws,{type:'error',error:'invalid_tip'}); return; }
        const conn = await pool.getConnection();
        try{
          await conn.beginTransaction();
          const [brows] = await conn.query('SELECT saldo_zafiros FROM usuarios WHERE idusuario=? FOR UPDATE',[uid]);
          if(!brows.length) throw new Error('buyer_not_found');
          const saldo = parseInt(brows[0].saldo_zafiros||0);
          if(saldo < zaf) throw new Error('insufficient');
          const commission_percent = parseInt(process.env.ENYOOI_COMMISSION||'20');
          const comision = Math.floor(zaf * commission_percent / 100);
          const creadora_recibe = zaf - comision;
          await conn.query('UPDATE usuarios SET saldo_zafiros = saldo_zafiros - ? WHERE idusuario=?',[zaf, uid]);
          await conn.query('UPDATE usuarios SET saldo_zafiros = saldo_zafiros + ? WHERE idusuario=?',[creadora_recibe, idcre]);
          await conn.query('INSERT INTO propinas (idusuario,idcreadora,zafiros,comision,creadora_recibe) VALUES (?,?,?,?,?)',[uid,idcre,zaf,comision,creadora_recibe]);
          await conn.commit();
          for(const client of rooms.get(room)){ send(client,{type:'tip_event',from_uid:user.idusuario,from_nick:user.nickname||user.usuario,to_creadora:idcre,amount:zaf,ts:Date.now()}); }
        }catch(err){ await conn.rollback(); send(ws,{type:'error',error:err.message||'tip_failed'}); } finally { conn.release(); }
      }
    });
    ws.on('close', ()=>{ if(rooms.has(room)){ rooms.get(room).delete(ws); for(const client of rooms.get(room)){ send(client,{type:'user_left',uid:user.idusuario,nickname:user.nickname||user.usuario}); } } });
  }catch(err){ ws.close(1011,'server_error'); }
});
initDB().then(()=> console.log('WS server running on port',WS_PORT)).catch(err=>{ console.error('DB init failed',err); process.exit(1); });
