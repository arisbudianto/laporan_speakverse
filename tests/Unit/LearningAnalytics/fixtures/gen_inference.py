import numpy as np, json, itertools
from scipy import stats
rng=np.random.default_rng(7)
out={}
# paired t, n=24
pre=np.round(rng.normal(55,12,24),1); post=np.round(pre+rng.normal(9,8,24),1)
r=stats.ttest_rel(post,pre); ci=r.confidence_interval(0.95)
d=post-pre
out['t']={'pre':pre.tolist(),'post':post.tolist(),'t':float(r.statistic),'p':float(r.pvalue),'df':int(r.df),'ci':[float(ci.low),float(ci.high)],'dz':float(d.mean()/d.std(ddof=1)),
 'g':float((1-3/(4*23-1))*d.mean()/d.std(ddof=1))}
# wilcoxon exact, no ties n=12
a=rng.normal(60,10,12); b=a+rng.normal(5,6,12)
w=stats.wilcoxon(b,a,method='exact')
out['w1']={'pre':a.tolist(),'post':b.tolist(),'p':float(w.pvalue)}
# wilcoxon with ties/zeros: brute force exact
pre2=[50,60,70,55,65,40,80,45,52,61]; post2=[55,60,78,55,60,52,90,50,52,70]
dd=np.array(post2)-np.array(pre2); nz=dd[dd!=0]; ab=np.abs(nz); rk=stats.rankdata(ab); obs=rk[nz>0].sum()
dist=[]
for signs in itertools.product([0,1],repeat=len(nz)):
    dist.append(sum(r for r,s in zip(rk,signs) if s))
dist=np.array(dist); p=min(1,2*min((dist<=obs+1e-9).mean(),(dist>=obs-1e-9).mean()))
out['w2']={'pre':pre2,'post':post2,'p':float(p)}
# normal approx n=40
a=rng.normal(60,10,40); b=np.round(a+rng.normal(3,7,40),0); a=np.round(a,0)
w=stats.wilcoxon(b,a,method='approx',correction=True)
out['w3']={'pre':a.tolist(),'post':b.tolist(),'p':float(w.pvalue)}
# kruskal
g=[rng.normal(m,10,n).round(0) for m,n in [(60,15),(66,22),(58,9)]]
k=stats.kruskal(*g)
out['kw']={'groups':[x.tolist() for x in g],'h':float(k.statistic),'p':float(k.pvalue)}
# ICC1 unbalanced
gs=[rng.normal(m,8,n) for m,n in [(60,12),(70,20),(55,9),(65,15)]]
allv=np.concatenate(gs); N=len(allv); a_=len(gs); grand=allv.mean()
ssb=sum(len(x)*(x.mean()-grand)**2 for x in gs); ssw=sum(((x-x.mean())**2).sum() for x in gs)
msb=ssb/(a_-1); msw=ssw/(N-a_); k0=(N-sum(len(x)**2 for x in gs)/N)/(a_-1)
out['icc']={'groups':[x.tolist() for x in gs],'icc':float((msb-msw)/(msb+(k0-1)*msw))}
print(json.dumps(out))
