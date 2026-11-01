import numpy as np, json
from scipy import stats
from sklearn.metrics import silhouette_score
rng=np.random.default_rng(21)
out={}
# Spearman with ties
x=rng.integers(0,12,30).astype(float); y=(x*0.7+rng.normal(0,3,30)).round(0)
r=stats.spearmanr(x,y); out['sp']={'x':x.tolist(),'y':y.tolist(),'rho':float(r.statistic),'p':float(r.pvalue)}
# silhouette: 3 blobs, labels given
pts=np.vstack([rng.normal(c,0.6,(15,3)) for c in ([0,0,0],[4,4,0],[0,5,5])]); lab=np.repeat([0,1,2],15)
lab[3]=1; lab[20]=2  # beberapa titik salah label
out['sil']={'x':pts.tolist(),'labels':lab.tolist(),'score':float(silhouette_score(pts,lab))}
# BKT forward reference (independent implementation)
def bkt(obs,l0,t,s,g):
    p=l0;ll=0
    for o in obs:
        if o: d=p*(1-s)+(1-p)*g; post=p*(1-s)/d
        else: d=p*s+(1-p)*(1-g); post=p*s/d
        ll+=np.log(d); p=post+(1-post)*t
    return float(p),float(ll)
seqs={'a':[1,0,1,1,1,0,1,1,1,1],'b':[0,0,1,0,1,1,1],'c':[0,0,0,0,0,0,0,0,0,0,0,0]}
prm=dict(l0=0.25,t=0.15,s=0.1,g=0.2)
out['bkt']={'prm':prm,'cases':{k:{'obs':v,'p':bkt(v,**prm)[0],'ll':bkt(v,**prm)[1]} for k,v in seqs.items()}}
# simulate students with known parameters for fit-recovery
def sim(n,L,T,S,G,m,seed):
    r=np.random.default_rng(seed);res=[]
    for _ in range(n):
        known=r.random()<L;seq=[]
        for _ in range(m):
            seq.append(int(r.random()<((1-S) if known else G)))
            if not known and r.random()<T: known=True
        res.append(seq)
    return res
true=dict(l0=0.2,t=0.2,s=0.1,g=0.2)
out['fit']={'true':true,'seqs':sim(150,0.2,0.2,0.1,0.2,12,5)}
# power-law curve: error=0.6*k^-0.7 deterministik -> 100 siswa tiap k (pengamatan buatan lewat proporsi)
print(json.dumps(out))
